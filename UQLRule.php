<?php

class UQLRule extends UQLBase{

    private $uql_entity_name;
    private $uql_alises_map;
    private $uql_rules_map;

    public function __construct($entity_name) {

        $this->uql_entity_name = $entity_name;
        $this->uql_alises_map  = new UQLMap();
        $this->uql_rules_map   = new UQLMap();
    }

    public function __call($function_name,$parameters) {

        $local_params_count = count($parameters);
        if($local_params_count == 0) return;

        $this->addRule($function_name, $parameters);
        return $this;
    }

    protected function addRule($field,$rule) {

        if(!$this->uql_rules_map->isElementExist($field))
            $this->uql_rules_map->addElement($field, new UQLMap());

        $local_rule = $this->uql_rules_map->findElement($field);
        $local_rule->addElement($rule[0]/*rule name*/,array('rule'=> $rule, 'is_active' => true));

        $this->uql_rules_map->addElement($field, $local_rule);
    }

    protected function setRuleActivation($field_name,$rule_name,$activation)
    {
         $local_rule = $this->uql_rules_map->findElement($field_name);
         
        if(!$local_rule)
            $this->error('You can not stop a rule for unknown field ('.$field_name.')');

        $target_rule = $local_rule->findElement($rule_name);
        if(!$target_rule)
            $this->error('You can not stop unknown rule ('.$rule_name.')');


        $local_rule->addElement($rule_name,array('rule'=>$target_rule['rule'],'is_active'=> $activation));
        $this->uql_rules_map->addElement($field_name, $local_rule);
    }

    public function startRules(/*$field_name,$rule_name*/)
    {
        $params_count = func_num_args();
        if($params_count < 2)
            $this->error('startRules needs 2 parameters at least');

        $rules_counts = $params_count - 1; // remove field name
        $parameters = func_get_args();
        if($rules_counts == 1)
        {
             $this->setRuleActivation($parameters[0],$parameters[1],true);
             return;
        }
        else
        {
            for($i = 0; $i < $rules_counts - 1; $i++)
                $this->setRuleActivation($parameters[0],$parameters[$i + 1],true);
        }
    }

    public function stopRules(/*$field_name,$rule_name*/)
    {
        $params_count = func_num_args();
        if($params_count < 2)
            $this->error('stopRules needs 2 parameters at least');

        $rules_counts = $params_count - 1; // remove field name
        $parameters = func_get_args();
        if($rules_counts == 1)
        {
             $this->setRuleActivation($parameters[0],$parameters[1],false);
             return;
        }
        else
        {
            for($i = 0; $i < $rules_counts - 1; $i++)
                $this->setRuleActivation($parameters[0],$parameters[$i + 1],false);
        }
    }

    public function getRulesByFieldName($field_name) {

        return $this->uql_rules_map->findElement($field_name);
    }

    public function addAlias($key, $value) {

        $this->uql_alises_map->addElement($key, $value);
    }

    public function getAlias($key) {

        return $this->uql_alises_map->findElement($key);
    }

    public function getRules() {
        return $this->uql_alises_map;
    }

    public function getEntityName() {
        return $this->uql_entity_name;
    }

    public function getAliases() {
        return $this->uql_alises_map;
    }

    public static function findRuleObject($entity) {
        
        $rule_object_name = sprintf(UQL_RULE_OBJECT_SYNTAX,$entity);

        if(isset($GLOBALS[$rule_object_name]))
            $rule_object = $GLOBALS[$rule_object_name];
        else
            $rule_object = null;

        return $rule_object;

    }

    public function __destruct() {
    
        $this->uql_entity_name = null;
        $this->uql_rules_map = null;
        $this->uql_alises_map = null;
    }
}

?>