<?php

/**
 * Configuration object that triggers customizable behavior.
 *
 * @warning This class is strongly defined: that means that the class
 *          will fail if an undefined directive is retrieved or set.
 * 
 * @note Many classes that could (although many times don't) use the
 *       configuration object make it a mandatory parameter.  This is
 *       because a configuration object should always be forwarded,
 *       otherwise, you run the risk of missing a parameter and then
 *       being stumped when a configuration directive doesn't work.
 */
class HTMLPurifier_Config
{
    
    /**
     * Two-level associative array of configuration directives
     */
    var $conf;
    
    /**
     * Reference HTMLPurifier_ConfigDef for value checking
     */
    var $def;
    
    /**
     * @param $definition HTMLPurifier_ConfigDef that defines what directives
     *                    are allowed.
     */
    function HTMLPurifier_Config(&$definition) {
        $this->conf = $definition->defaults; // set up, copy in defaults
        $this->def  = $definition; // keep a copy around for checking
    }
    
    /**
     * Convenience constructor that creates a default configuration object.
     * @return Default HTMLPurifier_Config object.
     */
    function createDefault() {
        $definition =& HTMLPurifier_ConfigDef::instance();
        $config = new HTMLPurifier_Config($definition);
        return $config;
    }
    
    /**
     * Retreives a value from the configuration.
     * @param $namespace String namespace
     * @param $key String key
     */
    function get($namespace, $key) {
        if (!isset($this->conf[$namespace][$key])) {
            trigger_error('Cannot retrieve value of undefined directive',
                E_USER_WARNING);
            return;
        }
        return $this->conf[$namespace][$key];
    }
    
    /**
     * Sets a value to configuration.
     * @param $namespace String namespace
     * @param $key String key
     * @param $value Mixed value
     */
    function set($namespace, $key, $value) {
        if (!isset($this->conf[$namespace][$key])) {
            trigger_error('Cannot set undefined directive to value',
                E_USER_WARNING);
            return;
        }
        if (is_string($value)) {
            // resolve value alias if defined
            if (isset($this->def->info[$namespace][$key]->aliases[$value])) {
                $value = $this->def->info[$namespace][$key]->aliases[$value];
            }
            if ($this->def->info[$namespace][$key]->allowed !== true) {
                // check to see if the value is allowed
                if (!isset($this->def->info[$namespace][$key]->allowed[$value])) {
                    trigger_error('Value not supported', E_USER_WARNING);
                    return;
                }
            }
        }
        $value = $this->def->validate($value,
                                      $this->def->info[$namespace][$key]->type);
        if ($value === null) {
            trigger_error('Value is of invalid type', E_USER_WARNING);
            return;
        }
        $this->conf[$namespace][$key] = $value;
    }
    
}

?>