SimplePie Plugin for CakePHP 2.x
=============

Informations
-------------------------------------------------------
Integrate the SimplePie Feed Parser into your Cakephp 2.x Application. This is based on a French Version which is based on the CakePHP 1.x (https://github.com/mcurry/simplepie) plugin.

Licence
-------------------------------------------------------
Something in French


Installation
-------------------------------------------------------
Add this to your `Config/bootstrap.php`

	CakePlugin::load('Simplepie',array('bootstrap'=>true));

Usage
-------------------------------------------------------
In your controller, add the SimplePie component.

	public $components = array('Simplepie.Simplepie');
	
	
Optionally, in your model, add the SimplePie behavior

  public $actsAs = array('Simplepie.Simplepie');
  
  
To Use:

	$this->Simplepie->feed('URL_DU_FLUX_RSS'); 
	
	$this->set($d);