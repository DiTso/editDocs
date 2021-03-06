<?php

if (IN_MANAGER_MODE != "true" || empty($modx) || !($modx instanceof DocumentParser)) {
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
if (!$modx->hasPermission('exec_module')) {
    header("location: " . $modx->getManagerPath() . "?a=106");
}
if(!is_array($modx->event->params)){
    $modx->event->params = array();
}
function str_in($str) {
    $tmp = explode(',', $str);
    foreach ($tmp as $k => $v) {
        $tmp[$k] = "'" . trim($v) . "'";
    }
    return implode(',', $tmp);
}

//Подключаем обработку шаблонов через DocLister
include_once(MODX_BASE_PATH.'assets/snippets/DocLister/lib/DLTemplate.class.php');
$dlt = DLTemplate::getInstance($modx);
$dlt->setTemplatePath('assets/modules/editdocs/tpl/');
$dlt->setTemplateExtension('tpl');

$moduleurl = 'index.php?a=112&id='.$_GET['id'].'&';
$action = isset($_GET['action']) ? $_GET['action'] : 'branch';

//site_content fields
$fields = '';
if (isset($modx->event->params['include_fields']) && $modx->event->params['include_fields'] != '') {
    $tmp = explode(',', $modx->event->params['include_fields']);
    foreach ($tmp as $field) {
		if ($field != 'id') {
			$fields .= '<option value="' . trim($field) . '">' . trim($field) . '</option>';
		}
    }
}
//tv-name list
$where_tv = '';
if (isset($modx->event->params['include_tvs']) && $modx->event->params['include_tvs'] != '') {
    $where_tv .= ' WHERE name IN (' . str_in($modx->event->params['include_tvs']) . ') ';
}
$query = $modx->db->query("SELECT name,caption FROM " . $modx->getFullTableName('site_tmplvars') . " " . $where_tv . " ORDER BY caption ASC");
$tvs = '';
while ($row = $modx->db->getRow($query)) {
    $tvs .= '<option value="' . $row['name'] . '">' . $row['caption'] . '</option>';
}

//templates list
$where_tmpl = '';
if (isset($modx->event->params['include_tmpls']) && $modx->event->params['include_tmpls'] != '') {
    $where_tmpl .= ' WHERE id IN (' . str_in($modx->event->params['include_tmpls']) . ') ';
}
$query2 = $modx->db->query("SELECT id,templatename FROM " . $modx->getFullTableName('site_templates') . $where_tmpl);
$tpl = '';
while ($row = $modx->db->getRow($query2)) {
    $tpl .= '<option value="' . $row['id'] . '">' . $row['templatename'] . '</option>';
}

$data = array ('tpl' => $tpl, 'fields' => $fields, 'tvs' => $tvs, 'moduleurl' => $moduleurl, 'manager_theme' => $modx->config['manager_theme'], 'manager_path' => $modx->getManagerPath(), 'base_url' => $modx->config['base_url'], 'session' => $_SESSION,'get' => $_GET, 'action' => $action , 'selected' => array($action => 'selected'));

if ($action == 'branch') {
    $outTpl = $dlt->parseChunk('@FILE:branch', $data);
}
if ($action == 'excel') {
    $outTpl = $dlt->parseChunk('@FILE:excel', $data);
}
if ($action == 'import') {
    $outTpl = $dlt->parseChunk('@FILE:import', $data);
}
if ($action == 'export') {
    $outTpl = $dlt->parseChunk('@FILE:export', $data);
}

$output = $dlt->parseChunk('@FILE:header', $data) . $outTpl . $dlt->parseChunk('@FILE:footer', $data);
echo $output;

?>