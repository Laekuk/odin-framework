<?php
#CreatedBy;Aaron;31OCT2014;Odin-Framework
class bolt_xml
{
	// strip all non alpha numeric characters
	function gen_rss2($channel_info,$xml_array)
	{
/*
	Its helpful to run this, if your RSS feed is being output to a browser:
	header('Content-Type: application/rss+xml;');

	example params (see http://static.userland.com/gems/backend/rssTwoExample2.xml for full list of key-value examples):
		$channel_info		= array(
			'title'				=> 'Example Blog!',
			'link'				=> 'http://example.com',
			'description'		=> 'This is my blog!!1j',
		);
		$items				= array(
			array(
				'title'			=> 'First',
				'link'			=> 'http://example.com/blog/1/',
				'description'	=> 'This is my first blog ever!',
				'pubDate'		=> 'Mon, 30 Sep 2002 01:56:02 GMT',
				'guid'			=> 'http://example.com/blog/1/'
			),
			array(
				'title'			=> 'Second',
				'link'			=> 'http://example.com/blog/2/',
				'guid'			=> 'http://example.com/blog/2/'
			),
		);
*/
		$dom		= new DOMDocument();
		#create the main rss tag
		$rss		= $dom->createElement('rss');
		#set RSS version
		$rss_ver	= $dom->createAttribute('version');
		$rss_ver->value	= '2.0';
		$rss->appendChild($rss_ver);
		$atom	= $dom->createAttribute('xmlns:atom');
		$atom->value	= 'http://www.w3.org/2005/Atom';
		$rss->appendChild($atom);
#		<atom:link href="http://dallas.example.com/rss.xml" rel="self" type="application/rss+xml" />
		
		$channel	= $dom->createElement('channel');
		if(!empty($channel_info))
		{
			foreach($channel_info as $k=>$v)
			{
				$ele	= $dom->createElement($k,$v);
				$channel->appendChild($ele);
			}
		}
		if(!empty($xml_array))
		{
			foreach($xml_array as $item_data)
			{
				if($item_data)
				{
					$item	= $dom->createElement('item');
					foreach($item_data as $item_key=>$item_value)
					{
						$ele	= $dom->createElement($item_key,$item_value);
						$item->appendChild($ele);
					}
					$channel->appendChild($item);
					$item	= NULL;
				}
			}
		}
		$rss->appendChild($channel);
		$dom->appendChild($rss);
		return $dom->saveXML();
	}
}
