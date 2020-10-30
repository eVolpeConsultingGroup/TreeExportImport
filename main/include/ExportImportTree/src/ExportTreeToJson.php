<?php


class ExportTreeToJson
{
    private $exported = [];
    protected $useNewId = true;
    private $hash = false;
    protected static $ignoredLinks = ['modified_user_link', 'assigned_user_link', "ev_alfrescoaccess_users"];
    protected static $notExportableFieldNames = ['id', 'team_set_id', 'acl_team_set_id'];
    protected static $notExportableFieldForModule = [
        'Users.user_hash' ,
        'Emails.outbound_email_id' ,
        'Notes.upload_id' ,
    ];

    public function export($module, $id, $depth = 3)
    {
        $bean = BeanFactory::getBean($module, $id);
        return $this->exportBean($bean, $depth);
    }

    protected function exportBean(SugarBean $bean, $depth = 3)
    {
        if ($this->isRecordExported($bean)) {
            return $this->getRecordBasicData($bean);
        }

        $record = $this->getRecordData($bean);
        if ($depth != 0) {
            $record['children'] = $this->getRecordChildren($bean, $depth);
        }
        return $record;
    }
    protected function getRecordBasicData($bean)
    {
        return [
            '_module_name' => $bean->module_name,
            '_id' => $this->translateId($bean->module_name, $bean->id),
        ];
    }

    protected function getRecordChildren($bean, $depth)
    {
        $record_children = [];
        foreach ($bean->get_linked_fields() as $link) {
            $link_name = $link['name'];
            if ($this->isBannedLinkName($link_name)) {
                continue;
            }
            if ($bean->load_relationship($link_name) || is_object($bean->$link_name)) {
                foreach ($bean->$link_name->getBeans() as $child) {
                    $linked_record = $this->exportBean($child, $depth - 1);
                    if (count($linked_record) > 0) {
                        $record_children[$link][] = $linked_record;
                    }
                }

            }
        }
        return $record_children;
    }
    protected function isBannedLinkName($link)
    {
        return in_array($link, static::$ignoredLinks);
    }
    protected function getRecordData($bean)
    {
        $record = $this->getRecordBasicData($bean);
        foreach ($bean->field_defs as $field_def) {
            $field_name = $field_def['name'];
            if (!$this->isFieldExportable($bean->module_name, $field_def)) {
                continue;
            }
            $value = $bean->{$field_name};            
            if ($this->isFieldIdType($field_def) && !empty($value)) {
                $value = $this->translateId($this->getModuleNameForIdField($field_def, $bean), $value);
            }
            $record[$field_name] = $value;
        }
        //FIXME Wyjątrk dla modułu - inna obsługa  może w przyszłosci jakać inna obsługa
        // user_name - przy ponownym eksprcie ale zmianie id-ków user_name wykrywa duplikaty
        if ($bean->module_name == 'Users') {
            $record['user_name'] = $record['_id'];
        }
        $this->setRecordAsExported($bean);

        return $record;
    }
    protected function setRecordAsExported($bean)
    {
        $this->exported[$bean->module_name . $bean->id] = true;
    }
    protected function isRecordExported($bean)
    {
        return isset($this->exported[$bean->module_name . $bean->id]);
    }
    protected function isFieldIdType($field_def)
    {
        return ((($field_def['type'] ?? "") == 'id') || (($field_def['dbType'] ?? "") == 'id'));
    }
    protected function getModuleNameForIdField($field_def, $bean)
    {
        $module_name = (new IdFieldToModuleName())->find($bean, $field_def);
        if (!empty($module_name)) {
            return $module_name;
        }
        echo "Some Bug please look to code ";
        print_r([$field_def, $bean->module_name, $bean->field_defs]);
        die();
    }
    protected function translateId($module, $id)
    {
        if (!$this->useNewId) {
            return $id;
        }
        if (!empty($id)) {
            return md5($module . $id . $this->getHash());
        }
    }
    protected function getHash()
    {
        if (empty($this->hash)) {
            $this->hash = date("Y-m-d H:i:s");
        }
        return $this->hash;
    }
    protected function isFieldExportable($module, $field_def)
    {
        if (isset($field_def['source']) && $field_def['source'] == 'non-db') {
            return false;
        }
        $name = $field_def['name'];
        $type = $field_def['type'];

        if (in_array($name, static::$notExportableFieldNames)) {
            return false;
        }

        if (in_array("$module.$name",static::$notExportableFieldForModule)) {
            return false;
        }

        if (in_array($type, ['link', 'relation'])) {
            return false;
        }
        return true;
    }
}

class IdFieldToModuleName
{
    protected static $mapModuleFieldToReletedModule = [
        'EmailParticipants.email_id' => 'Emails',
        'Users.reports_to_id' => 'Users',
        'Users.default_team' => 'Teams',
        'Emails.reply_to_id' => 'Emails',
        'Notes.email_id' => 'Emails',
        'Calls.repeat_parent_id' => 'Calls',
        'Meetings.repeat_parent_id' => 'Meetings',
        'Teams.associated_user_id' => 'Users',

    ];
    /**
     * Brak kontroli nad kolejnością wykonywania.
     *
     * @param SugarBean $bean
     * @param array $field_def
     * @return String|Null
     */
    public function find($bean, $field_def)
    {
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if ($this->callMethod($method, $bean, $field_def)) {
                return $this->last_result;
            }
        }
        return null;

    }
    /**
     * @deprecated
     * @param SugarBean $bean
     * @param array $field_def
     * @return String|Null
     */
    public function findInSemiOrder($bean, $field_def)
    {
        $runFirst = [
            'getByType',
            'getByModuleParams',
        ];
        $methods = array_flip(get_class_methods($this));
        foreach ($runFirst as $method) {
            if ($this->callMethod($method, $bean, $field_def)) {
                return $this->last_result;
            }
            unset($methods[$method]);
        }
        foreach ($methods as $method => $k) {
            if ($this->callMethod($method, $bean, $field_def)) {
                return $this->last_result;
            }
        }
        return null;

    }
    protected function callMethod($method, $bean, $field_def)
    {
        if (0 === strpos($method, 'getBy')) {
            $this->last_result = $this->$method($bean, $field_def);
            if (!empty($this->last_result)) {
                return true;
            }
        }
        return false;
    }
    protected function getByType($bean, $field_def)
    {
        if ('assigned_user_name' == $field_def['type']) {
            return 'Users';
        }
    }
    protected function getByModuleParams($bean, $field_def)
    {
        if (isset($field_def['module'])) {
            return $field_def['module'];
        }
    }
    protected function getByNameId($bean, $field_def)
    {
        if ($field_def['name'] == 'id') {
            return $bean->module_name;
        }
    }
    protected function getByNameCreatedBy($bean, $field_def)
    {
        if ($field_def['name'] == 'created_by') {
            return 'Users';
        }
    }
    protected function getByNameParent($bean, $field_def)
    {
        if ($field_def['name'] == 'parent_id') {
            return $bean->parent_type;
        }
    }
    protected function getByNameAcl($bean, $field_def)
    {
        if ($field_def['name'] == 'acl_role_set_id') {
            return 'ACLRoles';
        }
    }
    protected function getByConfig($bean, $field_def)
    {
        $moduleField = "{$bean->module_name}.{$field_def['name']}";
        if (isset(static::$mapModuleFieldToReletedModule[$moduleField])) {
            return static::$mapModuleFieldToReletedModule[$moduleField];
        }
    }
    protected function getByGroup($bean, $field_def)
    {
        if (isset($field_def['group']) && isset($bean->field_defs[$field_def['group']]['module'])) {
            return $bean->field_defs[$field_def['group']]['module'];
        }
    }
    protected function getByIdLink($bean, $field_def)
    {

        $links = $bean->get_linked_fields();
        $links_id_link = array_column($links, 'name', 'id_link');
        // SR szalony zapis z tymi ?? null
        if (!empty($links[$links_id_link[$field_def['name'] ?? null] ?? null]['module'])) {
            return $links[$links_id_link[$field_def['name']]]['module'];
        }
    }
    protected function getByGroupReleted($bean, $field_def)
    {
        $links_group = array_filter($bean->field_defs, function ($item) use ($field_def) {
            return $item['type'] == 'relate' && isset($item['group']) && $item['group'] == $field_def['group'];
        });
        if (!empty($links_group[$field_def['group'] ?? null]['module'])) {
            return $links_group[$field_def['group']]['module'];
        }
    }
    protected function getByIdName($bean, $field_def)
    {
        $links_group = array_filter($bean->field_defs, function ($item) use ($field_def) {
            return $item['type'] == 'relate' && isset($item['id_name']) && $item['id_name'] == $field_def['name'];
        });
        if (count($links_group) > 0) {
            $links_group = array_values($links_group);
            return $links_group[0]['module'];
        }
    }
}
