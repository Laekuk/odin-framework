<?php
#CreatedBy;Aaron;31OCT2014;Odin-Framework
class bolt_xml
{
	// strip all non alpha numeric characters
	function make_rss2($channel_info,$xml_array)
	{
/*
	Its helpful to run this, if your RSS feed is being output to a browser:
	header('Content-Type: rss2; charset=utf-8', true);


	example params (see http://static.userland.com/gems/backend/rssTwoExample2.xml for full list of key-value examples):
		$channel_info		= array(
			'title'				=> 'Second',
			'link'				=> 'http://example.com/',
			'description'		=> 'This is my blog!!1j',
		);
		$xml_array			= array(
			array(
				'title'			=> 'Second',
				'link'			=> 'http://example.com/blog/',
				'description'	=> 'This is my first blog ever!',
				'pubDate'		=> 'Mon, 30 Sep 2002 01:56:02 GMT',
				'guid'			=> 'http://example.com/blog/second-blog/',
			),
			array(
				'title'			=> 'Second',
				'link'			=> 'http://example.com/blog/',
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
		
		$channel	= $dom->createElement('channel');
		if(!empty($channel_info))
		{
			foreach($channel_info as $k=>$v)
				{ $channel->appendChild($dom->createElement($k,$v)); }
		}
		if(!empty($xml_array))
		{
			foreach($xml_array as $item_data)
			{
				if($item_data)
				{
					$item	= $dom->createElement('item');
					foreach($item_data as $item_key=>$item_value)
						{ $item->appendChild($dom->createElement($item_key,$item_value)); }
					$channel->appendChild($item);
				}
			}
		}
		$rss->appendChild($channel);
		$dom->appendChild($rss);
		return $dom->saveHTML();
	}
}
