<?
#CreatedBy;Weston;30OCT2015;Odin-Framework
// USAGE:
// 	---- remove bad tags and attributes, find possible xss attacks and remove them.
#  $mixed = $odin->xss->clean($mixed);
// 	---- same as above but also converts markup into special characters
#  $mixed = $odin->xss->sanitize($mixed);
class bolt_xss
{
	function __construct($conf)
	{
		$this->conf			= $conf;
		$this->tags_method	= 0;
		$this->attr_method	= 0;
		$this->xss_auto		= 1;
	}
	// this will htmlspecialchars html, OWASP secure
	function sanitize($str)
		{ return $this->clean($str,true); }
	// ->clean($str); clean out possible XSS attacks and return html (without htmlspecialchars)
	function clean($str,$special_chars=false)
	{
		// recurse through the array until a string is found
		if(is_array($str))
		{
			$ret = array();
			foreach($str as $k=>$v)
				{ $ret[$k] = $this->clean($v,$special_chars); }
			$str = $ret;
		}
		elseif(is_string($str))
		{
			// decode all characters
			$str = $this->decode($str);
			// replace non utf-8 chars
			$str = $this->fix_chars($str);
			// strip blacklisted tags and content
			$str = $this->bad_tags($str);
			// remove non whitelisted tags
			$str = $this->good_tags($str);
			// XSS filter
			$str = $this->xss_filter($str);
			// kills html tags all together
			// 	this is a OWASP xss recommendation
			//  but converts all HTML tags into special characters
			if($special_chars)
				{ $str = $this->specialchars($str); }
		}
		return $str;
	}
	// helpers
	function specialchars($str)
	{
		if(is_string($str)) { return htmlspecialchars($str, ENT_COMPAT | ENT_HTML401, "UTF-8"); }
		return $this->recurse($str,'specialchars');
	}
	function decode($str)
	{
		if(is_string($str)) { return html_entity_decode($str, ENT_COMPAT | ENT_HTML401, "UTF-8"); }
		return $this->recurse($str,'decode');
	}
	function encode($str)
	{
		if(is_string($str)) { return htmlentities($str, ENT_COMPAT | ENT_HTML401, "UTF-8"); }
		return $this->recurse($str,'decode');
	}
	function recurse()
	{
		$args	= func_get_args();
		if(count($args)<2)
			{ return false; }
		$mixed	= array_shift($args);
		$method	= array_shift($args);
		if(is_array($mixed))
		{
			$ret = array();
			foreach($mixed as $k=>$v)
				{ $ret[$k] = $this->recurse($v,$method); }
			$mixed = $ret;
		}
		elseif(is_string($mixed))
			{ $mixed = $this->{$method}($mixed); }
		return $mixed;
	}
	// only whitelisted tags
	function good_tags($str)
	{
		if(is_string($str)) { return strip_tags($str,'<'.implode('><',$this->conf->tag_whitelist).'>'); }
		return $this->recurse($str,'good_tags');
	}
	// strip blacklisted tags and their content
	function bad_tags($str,$keep_tags=array())
	{
		if(is_string($str))
		{
			$tags = array_diff($this->conf->tag_blacklist,$keep_tags);
			
			foreach($tags as $tag)
				{ $str = preg_replace('/<'.$tag.'[^>]*>([\s\S]*?)<\/'.$tag.'[^>]*>/i','',$str); }
			return $str;
		}
		return $this->recurse($str,'bad_tags');
	}
	
	// make some special characters safe
	function fix_chars($str)
	{
		if(is_string($str))
		{
			$str = strtr($str,
				"\x82\x83\x84\x85\x86\x87\x89\x8a\x8b\x8c\x8e\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9e\x9f",
				"'f\".**^\xa6<\xbc\xb4''\"\"---~ \xa8>\xbd\xb8\xbe"
			);
			$rep = array(
				130=>',',	131=>'NLG',	132=>'"',	133=>'...',	134=>'**',	135=>'***',	136=>'^',
				137=>'o/oo',138=>'Sh',	139=>'<',	140=>'OE',	145=>"'",	146=>"'",	147=>'"',
				148=>'"',	149=>'-',	150=>'-',	151=>'--',	152=>'~',	153=>'(TM)',154=>'sh',
				155=>'>',	156=>'oe',	159=>'Y'
			);
			foreach($rep as $k=>$v)
				{ $str = str_replace(chr($k),$v,$str); }
			return $str;
		}
		return $this->recurse($str,'fix_chars');
	}
	
	
	/***
	* 
	* This code modified from a class written by: Daniel Morris
	* Modified, updated and maintained by: Avallo Web Development
	* @modified:		10-30-2015
	* 
	***/
	/***
	* @class:			InputFilter (PHP5-Strict with comments)
	* @project:			PHP Input Filter
	* @version:			1.2.2_php5
	* @author:			Daniel Morris
	* @contributors:	Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
	* @copyright:		Daniel Morris
	* @email:			dan@rootcube.com
	* @license:			GNU General Public License (GPL)

	* @link:			http://www.phpclasses.org/package/2189-PHP-Filter-out-unwanted-PHP-Javascript-HTML-tags-.html
	* @Note: 			This has been modified by us from its original contents.
	***/

	protected function xss_filter($source)
	{
		$loop_counter=0;
		// provides nested-tag protection
		while($source != $this->filter_tags($source))
		{
			$source = $this->filter_tags($source);
			$loop_counter++;
		}
		return $source;
	}
	protected function filter_tags($source)
	{
		// filter pass setup
		$pre_tag = null;
		$post_tag = $source;
		// find initial tag's position
		$tag_open_start = strpos($source, '<');
		// interate through string until no tags left
		while($tag_open_start !== false)
		{
			// process tag interatively
			$pre_tag 			.= substr($post_tag, 0, $tag_open_start);
			$post_tag 			= substr($post_tag, $tag_open_start);
			$from_tag_open 		= substr($post_tag, 1);
			// end of tag
			$tag_open_end 		= strpos($from_tag_open, '>');
			if($tag_open_end === false)
				{ break; }
			// next start of tag (for nested tag assessment)
			$tag_open_nested	= strpos($from_tag_open, '<');
			if(($tag_open_nested !== false) && ($tag_open_nested < $tag_open_end))
			{
				$pre_tag		.= substr($post_tag, 0, ($tag_open_nested+1));
				$post_tag		= substr($post_tag, ($tag_open_nested+1));
				$tag_open_start	= strpos($post_tag, '<');
				continue;
			}
			$tag_open_nested	= (strpos($from_tag_open, '<') + $tag_open_start + 1);
			$current_tag		= substr($from_tag_open, 0, $tag_open_end);
			$tag_length			= strlen($current_tag);
			if(!$tag_open_end)
			{
				$pre_tag 		.= $post_tag;
				$tag_open_start	= strpos($post_tag, '<');
			}
			// iterate through tag finding attribute pairs - setup
			$tag_left		= $current_tag;
			$attr_set		= array();
			$current_space	= strpos($tag_left, ' ');
			// is end tag
			if(substr($current_tag, 0, 1) == "/")
			{
				$is_close_tag	= true;
				list($tag_name)	= explode(' ', $current_tag);
				$tag_name		= substr($tag_name, 1);
				// is start tag
			}
			else
			{
				$is_close_tag	= false;
				list($tag_name)	= explode(' ', $current_tag);
			}
			// excludes all "non-regular" tagnames or no tagname or remove if xssauto is on and tag is blacklisted
			if((!preg_match("/^[a-z][a-z0-9]*$/i",$tag_name)) || (!$tag_name) || ((in_array(strtolower($tag_name), $this->conf->tag_blacklist)) && ($this->xss_auto)))
			{
				$post_tag		= substr($post_tag, ($tag_length + 2));
				$tag_open_start	= strpos($post_tag, '<');
				// don't append this tag
				continue;
			}
			// this while is needed to support attribute values with spaces in!
			while ($current_space !== false)
			{
				$from_space		= substr($tag_left, ($current_space+1));
				$next_space		= strpos($from_space, ' ');
				$open_quotes	= strpos($from_space, '"');
				$close_quotes	= strpos(substr($from_space, ($open_quotes+1)), '"') + $open_quotes + 1;
				// another equals exists
				if(strpos($from_space, '=') !== false)
				{
					// opening and closing quotes exists
					if(($open_quotes !== false) && (strpos(substr($from_space, ($open_quotes+1)), '"') !== false))
						{ $attr = substr($from_space, 0, ($close_quotes+1)); }
					// one or neither exist
					else
						{ $attr = substr($from_space, 0, $next_space); }
					// no more equals exist
				}
				else
					{ $attr = substr($from_space, 0, $next_space); }
				// last attr pair
				if(!$attr)
					{ $attr = $from_space; }
				// add to attribute pairs array
				$attr_set[] = $attr;
				// next inc
				$tag_left		= substr($from_space, strlen($attr));
				$current_space	= strpos($tag_left, ' ');
			}
			// appears in array specified by user
			$tag_found = in_array(strtolower($tag_name), $this->conf->tag_whitelist);
			// remove this tag on condition
			if((!$tag_found && $this->tags_method) || ($tag_found && !$this->tags_method))
			{
				// reconstruct tag with allowed attributes
				if(!$is_close_tag)
				{
					$attr_set 	= $this->filter_attr($attr_set);
					$pre_tag 	.= '<' . $tag_name;
					for ($i = 0; $i < count($attr_set); $i++)
						{ $pre_tag .= ' ' . $attr_set[$i]; }
					// reformat single tags to xhtml
					if(strpos($from_tag_open, "</" . $tag_name))
						{ $pre_tag .= '>'; }
					else
						{ $pre_tag .= ' />'; }
					// just the tagname
				}
				else
					{ $pre_tag .= '</' . $tag_name . '>'; }
			}
			// find next tag's start
			$post_tag		= substr($post_tag, ($tag_length + 2));
			$tag_open_start	= strpos($post_tag, '<');
		}
		// append any code after end of tags
		$pre_tag .= $post_tag;
		return $pre_tag;
	}

	protected function filter_attr($attr_set)
	{
		$new_set = array();
		// process attributes
		for ($i = 0, $count=count($attr_set); $i<$count; $i++)
		{
			// skip blank spaces in tag
			if(!$attr_set[$i])
				{ continue; }
			// split into attr name and value
			$attr_sub_set		= explode('=', trim($attr_set[$i]));
			$attr_name			= array_shift($attr_sub_set);
			list($attr_name)	= explode(' ', $attr_name);
			$attr_value			= implode("=", $attr_sub_set);
			$attr_sub_set		= array($attr_name,$attr_value);
			// removes all "non-regular" attr names and also attr blacklisted
			if(
				(!preg_match("/^[a-z]*$/i",$attr_sub_set[0])) ||
				(
					($this->xss_auto) &&
					(
						(in_array(strtolower($attr_sub_set[0]), $this->conf->attr_blacklist)) ||
						(substr($attr_sub_set[0], 0, 2) == 'on')
					)
				)
			)
				{ continue; }
			// xss attr value filtering
			if($attr_sub_set[1])
			{
				// strips unicode, hex, etc
				$attr_sub_set[1]	= str_replace('&#', '', $attr_sub_set[1]);
				// strip normal newline within attr value
				$attr_sub_set[1]	= preg_replace('/\s+/', '', $attr_sub_set[1]);
				// strip double quotes
				$attr_sub_set[1]	= str_replace('"', '', $attr_sub_set[1]);
				// [requested feature] convert single quotes from either side to doubles (_single quotes shouldn't be used to pad attr value)
				if((substr($attr_sub_set[1], 0, 1) == "'") && (substr($attr_sub_set[1], (strlen($attr_sub_set[1]) - 1), 1) == "'"))
					{ $attr_sub_set[1] = substr($attr_sub_set[1], 1, (strlen($attr_sub_set[1]) - 2)); }
				// strip slashes
				$attr_sub_set[1]	= stripslashes($attr_sub_set[1]);
			}
			// auto strip attr's with "javascript:
			$atrlc = strtolower($attr_sub_set[1]);
			if(
				(
					(strpos($atrlc, 'expression') !== false) &&
					(strtolower($attr_sub_set[0]) == 'style')
				) ||
				(strpos($atrlc, 'javascript:')	!== false) ||
				(strpos($atrlc, 'behaviour:')	!== false) ||
				(strpos($atrlc, 'vbscript:')	!== false) ||
				(strpos($atrlc, 'mocha:')		!== false) ||
				(strpos($atrlc, 'livescript:')	!== false)
			)
				{ continue; }
			
			// if matches user defined array
			$attr_found	= in_array(strtolower($attr_sub_set[0]), $this->conf->attr_whitelist);
			// keep this attr on condition
			if((!$attr_found && $this->attr_method) || ($attr_found && !$this->attr_method))
			{
				// attr has value
				if($attr_sub_set[1]) 
					{ $new_set[] = $attr_sub_set[0] . '="' . $attr_sub_set[1] . '"'; }
				// attr has decimal zero as value
				elseif($attr_sub_set[1] == "0") 
					{ $new_set[] = $attr_sub_set[0] . '="0"'; }
				// reformat single attributes to xhtml
				else 
					{ $new_set[] = $attr_sub_set[0] . '="' . $attr_sub_set[0] . '"'; }
			}
		}
		return $new_set;
	}
}