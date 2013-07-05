<?php

class Module extends ModuleCore
{
  public static function getInstanceByName($module_name)
  {
    if (!Validate::isModuleName($module_name))
    {
      if (_PS_MODE_DEV_)
        die(Tools::displayError($module_name.' is not a valid module name.'));
      return false;
    }

    if (!isset(self::$_INSTANCE[$module_name]))
    {
      // Require ModuleOverrie 
      require_once 'ModuleOverride.php';
      // and load the right classes (child and mother or just the mother)
      ModuleOverride::load($module_name);

      if (class_exists($module_name, false))
        return self::$_INSTANCE[$module_name] = new $module_name;
      return false;
    }
    return self::$_INSTANCE[$module_name];
  }
}