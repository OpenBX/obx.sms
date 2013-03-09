<?
/*****************************************
 ** @vendor A68 Studio                  **
 ** @mailto info@a-68.ru                **
 ** @time 16:18                       **
 ** @user tashiro                       **
 *****************************************/
class A68_BXAdminUtils extends A68_CMessagePool{

	protected $_arChain = array();

	function __construct() {
		$this->_arChain = array(
			"a68_market" => array(
				"TEXT" => GetMessage("A68_MARKET_NAME"),
				"LINK" => "a68_market_index.php",
				"DEPTH" => 0,
				"PARENT" => null,
				"IS_PARENT" => true),
			"a68_market_orders" => array(
				"TEXT" => GetMessage("A68_MARKET_ORDERS"),
				"LINK" => "a68_market_orders.php",
				"DEPTH" => 1,

			),
		);
	}


	public static function setChain() {
		
	}
}
?>
