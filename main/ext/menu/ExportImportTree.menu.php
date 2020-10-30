<?php

if (!isset($module)) {
    global $module;
}
global $app_strings, $current_user;

if ($current_user->id === '1' && !in_array($module, ['Administration'])) {

    $module_menu[] = array("javascript:void(document.location='index.php?entryPoint=ExportImportTreeExport&export_module={$module}&id='+$('#EditView [name=record], #formDetailView [name=record]').val())()", $app_strings['LNK_EXPORTIMPORTTREE_EXPORT']);
    $module_menu[] = array('index.php?entryPoint=ExportImportTreeImport', $app_strings['LNK_EXPORTIMPORTTREE_IMPORT']);
}
