# Prestashop override module PHP

Allow overrrided prestashop module php in `themes/[YOURTHEME]/modules/[THEMODULE]/[THEMODULE].php`


## Installation

It's very simple, clone (or download) the two classes and paste into `override/module/` director and it's over.
very simple right?

## Usage

Create the same filename of the original module

`themes/my_theme/modules/blocksearch/blocksearch.php`

Declare you class and extend the mother with added Module suffix

```php
<?php
class BlockSearch extends BlockSearchModule
{

}
```

And finaly write your code 

```php
<?php
class BlockSearch extends BlockSearchModule
{
  public function install()
  {
    if (!parent::install() || !$this->registerHook('displayLeftColumn'))
      return false;
    return true;
  }

  public function hookLeftColumn($params)
  {
    if (!$this->isCached('blocksearch.tpl', $this->getCacheId()))
    {
      $this->calculHookCommon($params);
      $this->smarty->assign('blocksearch_type', 'block');
    }
    return $this->display(__FILE__, 'blocksearch.tpl', $this->getCacheId());
  }
}
```