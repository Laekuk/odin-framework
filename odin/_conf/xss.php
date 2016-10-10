<?
class conf_bolt_xss
{
	function __construct()
	{
		$this->tag_whitelist	= array(
			"em","strong","br","p","ul","ol","li","dl","dd","dt","h1","h2","h3","h4","h5","h6",
			"blockquote","address","div","cite","span",'a','acronym','code',
			'caption','table','thead','tbody','tr','th','td','tfoot',
			'fieldset','form','img','input','label','select','option','textarea','pre','button','sub','sup'
		);
		$this->attr_whitelist	= array(
			"class","id","placeholder","href","type","name","src"
		);
		$this->tag_blacklist = array(
			'applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 
			'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 
			'object', 'script', 'style', 'title', 'xml'
		);
		$this->attr_blacklist= array(
			'action', 'background', 'codebase', 'dynsrc', 'lowsrc',
		);
	}
}
$conf = new conf_bolt_xss();