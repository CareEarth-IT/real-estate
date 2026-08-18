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
            throw new RuntimeException('テンプレートが見つかりません。');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        if ($tempPath === false || ! copy($templatePath, $tempPath)) {
            throw new RuntimeException('テンプレートのコピーに失敗しました。');
        }

        $zip = new ZipArchive;
        if ($zip->open($tempPath) !== true) {
            @unlink($tempPath);
            throw new RuntimeException('テンプレートを開けませんでした。');
        }

        $sheetXml = $zip->getFromName(self::SHEET_PATH);
        if (! is_string($sheetXml) || $sheetXml === '') {
            $zip->close();
            @unlink($tempPath);
            throw new RuntimeException('シートを読み込めませんでした。');
        }

        $filledXml = $this->applyCells($sheetXml, $cells);
        $zip->deleteName(self::SHEET_PATH);
        $zip->addFromString(self::SHEET_PATH, $filledXml);
        $zip->close();

        $binary = file_get_contents($tempPath);
        @unlink($tempPath);

        if ($binary === false || $binary === '') {
            throw new RuntimeException('ファイルの作成に失敗しました。');
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
            throw new RuntimeException('シートの解析に失敗しました。');
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('m', self::SPREADSHEET_NS);

        foreach ($cells as $address => $cell) {
            $this->writeCell($dom, $xpath, $address, $cell['value'], (bool) ($cell['numeric'] ?? false));
        }

        $xml = $dom->saveXML();
        if (! is_string($xml) || $xml === '') {
            throw new RuntimeException('シートの保存に失敗しました。');
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
            $node = $this->createCell($dom, $xpath, $address);
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

    private function createCell(DOMDocument $dom, DOMXPath $xpath, string $address): DOMElement
    {
        [$rowIndex, $colIndex] = $this->addressToIndex($address);
        $rowNumber = (string) ($rowIndex + 1);
        $rows = $xpath->query('//m:sheetData/m:row[@r="'.$rowNumber.'"]');
        $row = $rows?->item(0);

        if (! $row instanceof DOMElement) {
            $sheetData = $xpath->query('//m:sheetData')->item(0);
            if (! $sheetData instanceof DOMElement) {
                throw new RuntimeException("テンプレートにセル {$address} を作成できません。");
            }

            $row = $dom->createElementNS(self::SPREADSHEET_NS, 'row');
            $row->setAttribute('r', $rowNumber);
            $sheetData->appendChild($row);
        }

        $cell = $dom->createElementNS(self::SPREADSHEET_NS, 'c');
        $cell->setAttribute('r', $address);

        $inserted = false;
        foreach ($row->childNodes as $child) {
            if (! $child instanceof DOMElement || $child->nodeName !== 'c') {
                continue;
            }
            $existing = $child->getAttribute('r');
            if ($existing !== '' && $this->addressToIndex($existing)[1] > $colIndex) {
                $row->insertBefore($cell, $child);
                $inserted = true;
                break;
            }
        }

        if (! $inserted) {
            $row->appendChild($cell);
        }

        return $cell;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function addressToIndex(string $address): array
    {
        if (! preg_match('/^([A-Za-z]+)(\d+)$/', trim($address), $matches)) {
            throw new RuntimeException("不正なセル位置です: {$address}");
        }

        $column = 0;
        foreach (str_split(strtoupper($matches[1])) as $letter) {
            $column = ($column * 26) + (ord($letter) - 64);
        }

        return [(int) $matches[2] - 1, $column - 1];
    }
}
