<?php
import ("Vendor.PHPExcel176.PHPExcel");
//require_once(APP_PATH . '/Admin/Common/feedback.php');
/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map 映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(&$data, $map = array('status' => array(1 => '正常', -1 => '删除', 0 => '禁用', 2 => '未审核', 3 => '草稿')))
{
    if ($data === false || $data === null) {
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}



/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{


    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }

    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree 原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array $list 过渡用的中间数组，
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array())
{
    if (is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if (isset($reffer[$child])) {
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby = 'asc');
    }
    return $list;
}

function import_lang($module_name)
{
    $file = APP_PATH . '/' . $module_name . '/Lang/' . LANG_SET . '.php';
    if (is_file($file))
        L(include $file);
}

/**
 * 根据条件字段获取数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @author huajie <banhuajie@163.com>
 */
function get_document_field($value = null, $condition = 'id', $field = null)
{
    if (empty($value)) {
        return false;
    }

    //拼接参数
    $map[$condition] = $value;
    $info = M('Model')->where($map);
    if (empty($field)) {
        $info = $info->field('*')->find();
    } else {
        $info = $info->getField($field);
    }
    return $info;
}
/**
 * 获取行为数据
 * @param string $id 行为id
 * @param string $field 需要获取的字段
 * @author huajie <banhuajie@163.com>
 */
function get_action($id = null, $field = null)
{
    if (empty($id) && !is_numeric($id)) {
        return false;
    }
    $list = S('action_list');
    if (empty($list[$id])) {
        $map = array('status' => array('gt', -1), 'id' => $id);
        $list[$id] = M('Action')->where($map)->field(true)->find();
    }
    return empty($field) ? $list[$id] : $list[$id][$field];
}
function array_column_i($input, $columnKey, $indexKey = NULL){
    if(!function_exists('array_column')){
        return array_column_p54($input, $columnKey, $indexKey);
    }
    return array_column($input, $columnKey, $indexKey);
    }
function array_column_p54($input, $columnKey, $indexKey = NULL)
{
    $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
    $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
    $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
    $result = array();

    foreach ((array)$input AS $key => $row)
    {
        if ($columnKeyIsNumber)
        {
            $tmp = array_slice($row, $columnKey, 1);
            $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
        }
        else
        {
            $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
        }
        if ( ! $indexKeyIsNull)
        {
            if ($indexKeyIsNumber)
            {
                $key = array_slice($row, $indexKey, 1);
                $key = (is_array($key) && ! empty($key)) ? current($key) : NULL;
                $key = is_null($key) ? 0 : $key;
            }
            else
            {
                $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
            }
        }

        $result[$key] = $tmp;
    }

    return $result;
}

function exportExcelSimple($title,$titleArr,$data){
    header("Content-Type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$title.".xls");
    header("Pragma:no-cache");
    header("Expires:0");
    $str="";
    echo iconv("utf-8","gbk",implode("\t",$titleArr)),"\n";
    foreach($data as $value){
        echo iconv("utf-8","gbk",implode("\t",$value)),"\n";
    }
}

/**
 * @param $title
 * @param $titleArr
 * @param $data
 * @throws Exception
 */
function exportExcel($title,$titleArr,$data,$widthArr=null,$desc,$filterStr){
    // Create new PHPExcel object
    $objPHPExcel = new \PHPExcel();
    // Set properties
    $objPHPExcel->getProperties()->setCreator("ctos")
        ->setLastModifiedBy("ctos")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("");
    // 设置默认字体和大小
    $objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', '宋体'));
    $styleArray1 = array(
        'font' => array(
            'bold' => true,
            'size'=>12,
            'color'=>array(
                'argb' => '00000000',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );

// 将A1单元格设置为加粗，居中
    $indexArr=[];
    //set title
    for($i=0;$i<count($titleArr);$i++){
        $indexArr[$i]=chr(65+$i);
        $index=chr(65+$i).'0';
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($index,$titleArr[$i]);
        $objPHPExcel->getActiveSheet()->getStyle($index)->applyFromArray($styleArray1);
        if($widthArr!=null){
            $objPHPExcel->getActiveSheet()->getColumnDimension($indexArr[$i])->setWidth($widthArr[$i]);
        }

    }
    //set value
    for($i=0;$i<count($data);$i++){
        for($j=0;$j<count($indexArr);$j++){
            // $value=$data[$i][$j]==null?"":$data[$i][$j];
            $objPHPExcel->getActiveSheet(0)->setCellValue($indexArr[$j].($i+1), $data[$i][$j]);
            $objPHPExcel->getActiveSheet()->getStyle($indexArr[$j].($i+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
    }

    //  sheet命名
    $objPHPExcel->getActiveSheet()->setTitle($title);
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    // excel头参数
    //防止ie乱码
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if(preg_match('/MSIE/',$ua)) {
        $title = str_replace('+','%20',urlencode($title));
    }

    header('Content-Type: application/vnd.ms-excel;charset=UTF-8');
    header('Content-Disposition: attachment;filename="'.$title.'('.date('Ymd-His').').xls"');  //日期为文件名后缀
    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式
    $objWriter->save('php://output');

}



