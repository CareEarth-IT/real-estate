<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class XlsxTemplateFiller
{
    private const SPREADSHEET_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const SHEET_PATH = 'xl/worksheets/sheet1.xml';

    /**
     * @param  array<string, array{value: string, numeric?: bool}>  $cells
     */
    public function fill(string $templatePath, array $cells): string
    {
        if (! is_file($templatePath)) {
            throw new RuntimeException('請求書テンプレートが見つかりません。');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'invoice_');
        if ($tempPath === false || ! copy($templatePath, $tempPath)) {
            throw new RuntimeException('請求書テンプレートのコピーに失敗しました。');
        }

        $zip = new ZipArchive;
        if ($zip->open($tempPath) !== true) {
            @unlink($tempPath);
            throw new RuntimeException('請求書テンプレートを開けませんでした。');
        }

        $sheetXml = $zip->getFromName(self::SHEET_PATH);
        if (! is_string($sheetXml) || $sheetXml === '') {
            $zip->close();
            @unlink($tempPath);
            throw new RuntimeException('請求書シートを読み込めませんでした。');
        }

        $filledXml = $this->applyCells($sheetXml, $cells);
        $zip->deleteName(self::SHEET_PATH);
        $zip->addFromString(self::SHEET_PATH, $filledXml);
        $zip->close();

        $binary = file_get_contents($tempPath);
        @unlink($tempPath);

        if ($binary === false || $binary === '') {
            throw new RuntimeException('請求書ファイルの作成に失敗しました。');
        }

        return $binary;
    }

    /**
     * @param  array<string, array{value: string, numeric?: bool}>  $cells
     */
    public function applyCells(string $sheetXml, array $cells): string
    {
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = true;
        $loaded = $dom->loadXML($sheetXml, LIBXML_NONET);
        if ($loaded !== true) {
            throw new RuntimeException('請求書シートの解析に失敗しました。');
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('m', self::SPREADSHEET_NS);

        foreach ($cells as $address => $cell) {
            $this->writeCell($dom, $xpath, $address, $cell['value'], (bool) ($cell['numeric'] ?? false));
        }

        $xml = $dom->saveXML();
        if (! is_string($xml) || $xml === '') {
            throw new RuntimeException('請求書シートの保存に失敗しました。');
        }

        return $xml;
    }

    public function readCell(string $sheetXml, string $address): string
    {
        $dom = new DOMDocument;
        $dom->loadXML($sheetXml, LIBXML_NONET);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('m', self::SPREADSHEET_NS);
        $nodes = $xpath->query('//m:c[@r="'.$address.'"]');
        $node = $nodes?->item(0);
        if (! $node instanceof DOMElement) {
            return '';
        }

        $type = $node->getAttribute('t');
        if ($type === 'inlineStr') {
            return trim($xpath->evaluate('string(.//m:t)', $node) ?: '');
        }

        return trim($xpath->evaluate('string(.//m:v)', $node) ?: '');
    }

    private function writeCell(
        DOMDocument $dom,
        DOMXPath $xpath,
        string $address,
        string $value,
        bool $numeric,
    ): void {
        $nodes = $xpath->query('//m:c[@r="'.$address.'"]');
        $node = $nodes?->item(0);
        if (! $node instanceof DOMElement) {
            throw new RuntimeException("請求書テンプレートにセル {$address} がありません。");
        }

        while ($node->firstChild) {
            $node->removeChild($node->firstChild);
        }

        if ($node->hasAttribute('t')) {
            $node->removeAttribute('t');
        }

        if ($value === '') {
            return;
        }

        if ($numeric) {
            $v = $dom->createElementNS(self::SPREADSHEET_NS, 'v');
            $v->appendChild($dom->createTextNode($value));
            $node->appendChild($v);

            return;
        }

        $node->setAttribute('t', 'inlineStr');
        $is = $dom->createElementNS(self::SPREADSHEET_NS, 'is');
        $t = $dom->createElementNS(self::SPREADSHEET_NS, 't');
        $t->appendChild($dom->createTextNode($value));
        $is->appendChild($t);
        $node->appendChild($is);
    }
}
