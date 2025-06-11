<?php

namespace Mt\Lib;

use Fw\InstanceTrait;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Excel
{

    use InstanceTrait;

    /**
     * 读取excel内容
     * @param $filePath
     * @param array $header
     * @param int $sheetIndex
     * @param int $beginRow
     * @return \Generator
     */
    public function read($filePath, array $header, $sheetIndex = 1, $beginRow = 1)
    {
        try {
            $PHPExcel = IOFactory::load($filePath);
            $objWorksheet = $PHPExcel->getSheet($sheetIndex - 1);
            $highestRow = $objWorksheet->getHighestRow(); // 取得总行数
            $title = array_values($header);
            $field = array_keys($header);

            //判断第一行表头是否正确
            foreach ($title as $titleKey => $titleValue) {
                if (($objWorksheet->getCellByColumnAndRow($titleKey + 1, $beginRow)->getValue()) != $titleValue) {
                    $error = '第' . ($titleKey + 1) . '列表头错误';
                }
            }
            if (empty($error)) {
                $beginRow += 1;
                for ($row = $beginRow; $row <= $highestRow; $row++) {
                    $data = [];
                    $noEmpty = false;
                    foreach ($field as $key => $value) {
                        $data[$value] = trim(strval($objWorksheet->getCellByColumnAndRow($key + 1, $row)->getValue()));
                        if ($value == "ymd") {
                            $data[$value]=gmdate('Ymd', Date::excelToTimestamp($data[$value]));
                        }
                        if ($data[$value] != "") {
                            $noEmpty = true;
                        }
                    }
                    if ($noEmpty) {
                        yield $data;
                    }
                }
            } else {
                yield new \Exception($error);
            }
        } catch (\Exception $exception) {
            yield $exception;
        }
    }

    /**
     * 上传到云存储下载
     * @param $filename
     * @param $data
     * @param array $header
     * @param bool $useMultiSheet
     * @return bool|string
     */
    public function exportUpload($filename, $data, $header = array(), $useMultiSheet = false)
    {
        $rank_key = date("YmdHis") . rand(1000, 9999);
        $oriFileName = $filename;
        $filename = app_env("app.log_path") . "/" . $rank_key . "_" . $filename;
        try {
            $this->export($filename, $data, $header, $justSaveFile = true, $useMultiSheet);
        } catch (\Exception $exception) {
            return false;
        }
        $Oss = Oss::getInstance();
        $file_url = $Oss->upload("common", $filename, $oriFileName);
        @unlink($filename);
        //十分钟后删除
        Task::getInstance()->delay(600, Oss::class, "delete", $file_url);
        return $file_url;
    }

    /**
     * 导出为Excel文件
     * 若有指定$header，且$header元素没指定key则$data列的顺序要与$header对应；
     * 若有指定$header,且$header元素有指定key，则key要与数据库表字段一致
     * @param $filename
     * @param $data
     * @param array $header
     * @param boolean $justSaveFile 是否只是保存为文件，false 会输出页面会下载header， true 只保存文件，不输出页面下载header
     * @param boolean $useMultiSheet 是否输出多个工作表,如果是,则$data的一级key为工作表的index
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export($filename, $data, $header = array(), $justSaveFile = false, $useMultiSheet = false)
    {
        if (!$useMultiSheet) {
            $data = [$data];
            $header = [$header];
        }

        //第一列 莫名bug 没空排查  先搁置  硬编码解决
        foreach ($header as $key => $value) {
            array_unshift($header[$key], '');
        }

        $spreadsheet = new Spreadsheet();
        $sheetRealIndex = 0;
        foreach ($data as $sheetIndex => $sheetData) {
            $autoSize = [];
            if ($sheetRealIndex != 0) {
                $workSheet = new Worksheet\Worksheet($spreadsheet, 'Worksheet' . $sheetRealIndex);
                $spreadsheet->addSheet($workSheet);
            }
            $spreadsheet->setActiveSheetIndex($sheetRealIndex);
            $objActSheet = $spreadsheet->getActiveSheet();
            if ($useMultiSheet && !is_numeric($sheetIndex)) {
                $objActSheet->setTitle($sheetIndex);//设置工作表标题
            }
            $span_arr = [];//合并单元格
            $row = 1;
            $sheetHeader = $header[$sheetIndex];
            $sheetRealIndex++;
            if ($sheetHeader) {
                $column = 0;
                foreach ($sheetHeader as $item) {
                    $item = $this->preFormatItem($item, $span_arr, $row, $column);
                    $objActSheet->setCellValueExplicitByColumnAndRow($column, $row, $item, DataType::TYPE_STRING2);
                    $objActSheet->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
                    $autoSize[$column] = $this->calculateWidth($item, $objActSheet->getParent()->getDefaultStyle()->getFont());
                    $column++;
                }
                $row++;
            }
            if ($sheetData) {
                $sheetHeaderKeys = array_keys($sheetHeader);
                if ($sheetData instanceof ExcelYield) {
                    $sheetData = $sheetData->getData();//大数据的时候 套用一个迭代生成器进行处理
                }
                foreach ($sheetData as $row_data) {
                    array_unshift($row_data, '');
                    $column = 0;
                    if ($sheetHeader) {
                        reset($row_data);
                        foreach ($sheetHeaderKeys as $key) {
                            $format = '';
                            if (is_numeric($key)) {
                                $item = current($row_data);
                                next($row_data);
                            } else {
                                $buffer_arr = explode('@', $key);
                                $item = $row_data[current($buffer_arr)];
                            }
                            //兼容合并单元格
                            $item = $this->preFormatItem($item, $span_arr, $row, $column);
                            if (is_array($item)) {
                                if (is_numeric($item[0]) && isset($item[1]) && $item[1] == "number_string") {
                                    $format = "string";
                                }
                                $item = $item[0];
                            }
                            $this->formatItem($objActSheet, $column, $row, $item, $format, $row_data);
                            $autoSize[$column] = max($autoSize[$column], $this->calculateWidth($item, $objActSheet->getParent()->getDefaultStyle()->getFont()));
                            $column++;
                        }
                    } else {
                        foreach ($row_data as $item) {
                            $item = $this->preFormatItem($item, $span_arr, $row, $column);
                            $number_string = false;
                            if (is_array($item)) {
                                if (is_numeric($item[0]) && isset($item[1]) && $item[1] == "number_string") {
                                    $number_string = true;
                                }
                                $item = $item[0];
                            }
                            $data_type = is_numeric($item) && $item <= 4294967295 ? DataType::TYPE_NUMERIC
                                : DataType::TYPE_STRING2;
                            $data_type = $number_string ? "string" : $data_type;
                            $objActSheet->setCellValueExplicitByColumnAndRow($column, $row, $item, $data_type);
                            $column++;
                        }
                    }
                    $row++;
                }
            }
            //合并单元格
            foreach ($span_arr as $spanSet) {
                $tempCellName = Cell\Coordinate::stringFromColumnIndex($spanSet["currentColumnIndex"]) . $spanSet["currentRowIndex"];
                $objActSheet->mergeCellsByColumnAndRow($spanSet["currentColumnIndex"], $spanSet["currentRowIndex"], $spanSet["targetColumnIndex"], $spanSet["targetRowIndex"])->getStyle($tempCellName)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }
            foreach ($autoSize as $column => $columnWidth) {
                $objActSheet->getColumnDimension(Cell\Coordinate::stringFromColumnIndex($column))->setWidth($columnWidth);
            }
        }
        $spreadsheet->setActiveSheetIndex(0);
        if (!$justSaveFile) {
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');
            header('Content-Disposition:inline;filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        } else {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filename);
        }
    }

    /**
     * 单元格合并
     * @param $item
     * @param $span_arr
     * @param $rowIndex
     * @param $columnIndex
     * @return mixed|string
     */
    protected function preFormatItem($item, &$span_arr, $rowIndex, $columnIndex)
    {
        if (!is_array($item)) {
            return $item;
        }
        if (!isset($item["value"])) {
            $current_item = "";
        } else {
            $current_item = $item["value"];
        }
        if ((isset($item["rowspan"]) && $item["rowspan"] > 1) || (isset($item["colspan"]) && $item["colspan"] > 1)) {
            $span_arr[] = [
                "currentRowIndex" => $rowIndex,
                "currentColumnIndex" => $columnIndex,
                "targetRowIndex" => $rowIndex + (isset($item["rowspan"]) && $item["rowspan"] > 1 ? ($item["rowspan"] - 1) : 0),
                "targetColumnIndex" => $columnIndex + (isset($item["colspan"]) && $item["colspan"] > 1 ? ($item["colspan"] - 1) : 0),
            ];
        }
        return $current_item;
    }

    /**
     * PhpSpreadsheet在计算自适应单元格的宽度时没有对中文做特殊处理,认为中文与字母所占宽度相同,导致导出带中文的表格时自适应宽度失效
     * 此处暂时使用自定义的宽度计算函数
     * @param $columnText
     * @param Font $pDefaultFont
     * @return int
     */
    private function calculateWidth($columnText, Font $pDefaultFont)
    {
        //计算宽度
        $columnWidth = (int)(8.26 * mb_strlen($columnText));
        //每个中文需要的宽度比字母多0.7倍左右
        preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $columnText, $chinese);
        $columnWidth += (int)(8.26 * count($chinese[0]) * 0.7);
        $columnWidth = Drawing::pixelsToCellDimension($columnWidth, $pDefaultFont);
        return $columnWidth;
    }

    /**
     * 把数据格式化为指定的格式
     * @param Worksheet\Worksheet $obj_sheet
     * @param $column
     * @param $row
     * @param $item
     * @param $format
     * @param $row_data
     * @return false|string
     */
    private function formatItem(&$obj_sheet, $column, $row, $item, $format, $row_data)
    {
        $format_arr = explode(':', $format);
        $format = $format_arr[0];
        switch ($format) {
            //把时间戳转成日期时间格式
            case 'datetime':
                $result = date('Y-m-d H:i:s', $item);
                $obj_sheet->setCellValueExplicitByColumnAndRow($column, $row, $result, DataType::TYPE_STRING2);
                break;
            //把时间戳转成日期格式
            case 'date':
                $result = date('Y-m-d', $item);
                $obj_sheet->setCellValueExplicitByColumnAndRow($column, $row, $result, DataType::TYPE_STRING2);
                break;
            //把秒数转成'N天N时N分N秒'时长格式
            case 'duration':
                $result = '';
                $day = (int)($item / 86400);
                $hour = (int)(($item % 86400) / 3600);
                $minute = (int)(($item % 3600) / 60);
                $sec = (int)($item % 60);
                if ($day > 0) {
                    $result .= $day . '天';
                }
                if ($day > 0 || $hour > 0) {
                    $result .= $hour . '时';
                }
                if ($day > 0 || $hour > 0 || $minute > 0) {
                    $result .= $minute . '分';
                }
                $result .= $sec . '秒';
                $obj_sheet->setCellValueExplicitByColumnAndRow($column, $row, $result, DataType::TYPE_STRING2);
                break;
            //把数据转成超链接，格式：text_item@url:link_item_key:tooltip_item_key
            case 'url':
                $result = $item;
                $link_item_key = isset($format_arr[1]) ? $format_arr[1] : '';
                $link_item = isset($row_data[$link_item_key]) ? $row_data[$link_item_key] : '';
                $tooltip_item_key = isset($format_arr[2]) ? $format_arr[2] : '';
                $tooltip_item = isset($row_data[$tooltip_item_key]) ? $row_data[$tooltip_item_key] : '';
                $obj_sheet->setCellValueExplicitByColumnAndRow($column, $row, $result, DataType::TYPE_STRING2);
                if ($result && $link_item) {
                    try {
                        $obj_sheet->getCellByColumnAndRow($column, $row)->getHyperlink()->setUrl($link_item);
                    } catch (\Exception $exception) {

                    }
                }
                if ($result && $link_item && $tooltip_item) {
                    try {
                        $obj_sheet->getCellByColumnAndRow($column, $row)->getHyperlink()->setTooltip($tooltip_item);
                    } catch (\Exception $exception) {

                    }
                }
                break;
            //不管是数字还是字符串都转为字符串格式
            case 'string':
                $result = $item;
                $obj_sheet->setCellValueExplicitByColumnAndRow($column, $row, $result, DataType::TYPE_STRING2);
                $obj_sheet->getStyleByColumnAndRow($column, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                break;
            default:
                $result = $item;
                $data_type = is_numeric($item) && $item <= 4294967295 ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING2;
                $obj_sheet->setCellValueExplicitByColumnAndRow($column, $row, $result, $data_type);
                break;
        }
        return $result;
    }

}