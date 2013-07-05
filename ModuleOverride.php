<?php

class ModuleOverride
{
  protected $modume_name;
  protected $module_core_path;
  protected $overrided_module;

  protected function __construct($moduleName)
  {
    $this->module_name = $moduleName;

    if(!defined('_PS_THEME_CACHE_DIR_'))
      define('_PS_THEME_CACHE_DIR_', _PS_THEME_DIR_.'cache'.DS.'modules'.DS);
    if(!is_dir(_PS_THEME_CACHE_DIR_))
      mkdir(_PS_THEME_CACHE_DIR_, 0777);

    $this->module_core_path = _PS_THEME_CACHE_DIR_.$this->module_name.'.core.php';

    if(file_exists(_PS_THEME_CACHE_DIR_.'module_index.php'))
      $this->overrided_module = include _PS_THEME_CACHE_DIR_.'module_index.php';
    
    if(!is_array($this->overrided_module))
      $this->overrided_module = array();
  }

  public static function load($moduleName)
  {
    $self = new self($moduleName);
    $self->_load();
  }

  protected function _load()
  {
    if(!file_exists(_PS_THEME_DIR_.'modules/'.$this->module_name.'/'.$this->module_name.'.php'))
      include_once _PS_MODULE_DIR_.$this->module_name.'/'.$this->module_name.'.php';
    else
    {
      $this->loadOverridedModule();
      require_once _PS_THEME_DIR_.'modules/'.$this->module_name.'/'.$this->module_name.'.php';
    }
  }



  protected function loadOverridedModule()
  {
    if(!file_exists($this->module_core_path) || $this->hasChanged())
      $this->generateCodeModuleFile();
    require_once $this->module_core_path;
  }

  protected function generateCodeModuleFile()
  {
    $moduleCore = preg_replace('/class\s+([a-zA-Z0-9_-]+)/', 'class $1Module', file_get_contents(_PS_MODULE_DIR_.$this->module_name.'/'.$this->module_name.'.php'));
    
    file_put_contents($this->module_core_path, $moduleCore, LOCK_EX);
    $this->overrided_module[$this->module_name] = filemtime(_PS_MODULE_DIR_.$this->module_name.'/'.$this->module_name.'.php');
    $this->generateIndex();
  }

  protected function hasChanged()
  {
    return !array_key_exists($this->module_name, $this->overrided_module) || $this->overrided_module[$this->module_name] != filemtime(_PS_MODULE_DIR_.$this->module_name.'/'.$this->module_name.'.php');
  }

  protected function generateIndex()
  {
    $content = '<?php return '.var_export($this->overrided_module, true).'; ?>';

    // Write classes index on disc to cache it
    $filename = _PS_THEME_CACHE_DIR_.'module_index.php';
    if ((file_exists($filename) && !is_writable($filename)) || !is_writable(dirname($filename)))
    {
      header('HTTP/1.1 503 temporarily overloaded');
      // Cannot use PrestaShopException in this context
      die($filename.' is not writable, please give write permissions (chmod 666) on this file.');
    }
    else
    {
      // Let's write index content in cache file
      // In order to be sure that this file is correctly written, a check is done on the file content
      $loop_protection = 0;
      do
      {
        $integrity_is_ok = false;
        file_put_contents($filename, $content, LOCK_EX);
        if ($loop_protection++ > 10)
          break;

        // If the file content end with PHP tag, integrity of the file is ok
        if (preg_match('#\?>\s*$#', file_get_contents($filename)))
          $integrity_is_ok = true;
      }
      while (!$integrity_is_ok);

      if (!$integrity_is_ok)
      {
        file_put_contents($filename, '<?php return array(); ?>', LOCK_EX);
        // Cannot use PrestaShopException in this context
        die('Your file '.$filename.' is corrupted. Please remove this file, a new one will be regenerated automatically');
      }
    }
  }
}