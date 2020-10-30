<?php

function post_install()
{
    runTwelveQuickRepairAndRebuild();

}

function runTwelveQuickRepairAndRebuild()
{

    $autoexecute = true;
    $show_output = true;

    $sapi_type = php_sapi_name();
    if (substr($sapi_type, 0, 3) == 'cli') {
        $show_output = false;
    }

    require_once "modules/Administration/QuickRepairAndRebuild.php";
    $repair = new RepairAndClear();
    $repair->repairAndClearAll(array(
        'clearAll',
    ), array(
        translate('LBL_ALL_MODULES'),
    ), $autoexecute, $show_output);
}
