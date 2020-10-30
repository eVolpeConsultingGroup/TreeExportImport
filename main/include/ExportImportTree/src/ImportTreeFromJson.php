<?php

class ImportTreeFromJson// ImportTreeFromJson

{
    public function import($json)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $date = json_decode($json, true);
        $this->importArray($date);
    }
    protected function importArray($date)
    {
        if (!isset($date['_id']) || !isset($date['_module_name'])) {
            return;
        }
        if ('OAuthTokens' == $date['_module_name'] || 'OAuthKeys' == $date['_module_name']) {
            return;
        }
        // echo "<pre>";echo print_r($date, 1);echo "</pre>";
        $module = $date['_module_name'];
        $id = $date['_id'];
        $bean = BeanFactory::getBean($module, $id);
        if ($bean->id !== $id) {
            $bean = BeanFactory::newBean($module);
            $bean->new_with_id = true;
            $bean->id = $id;
            if ($module == 'Users') {
                $_REQUEST['Users1emailAddress'] = "{$id}@{$id}.local";
            }
        }
        $save = false;
        foreach ($date as $k => $v) {
            if ('_module_name' == $k || '_id' == $k) {
                continue;
            }
            if (isset($bean->field_defs[$k]) && $bean->$k != $v) {
                $bean->$k = $v;
                $save = true;
            }
        }
        if ($save) {
            $bean->processed = true;
            $bean->save(false);
        }
        if (isset($date['children'])) {
            foreach ($date['children'] as $link => $records) {
                foreach ($records as $record) {
                    $this->importArray($record);
                    $bean->load_relationship($link);
                    $bean->$link->add($record['_id']);
                }
            }
        }
    }
}
