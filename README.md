<h1>Odin-Framework</h1>
<hr />

This is lightweight (simplistic) php framework that uses a hybrid both Functional and Object Oriented programming styles.
The goal is to have a tiny footprint with no server modifications.

This framework is intended to run on an Apache server (for now) in PHP v5.6

<hr />

<h2>Setup</h2>
<ol>
	<li>Download the codebase and place the "odin" folder somewhere in your website</li>
	<li>From any file require_once() the "fury.php" file, which is located in the "odin"" folder</li>
	<li>You now have access to the <strong>$odin</strong> variable and may use it to call any method in any of the bolts (libraries)</li>
</ol>

<hr />

<h2>Example</h2>
<strong>$odin</strong>->array->overwrite_merge_recursive($arrayA,$arrayB);

<h2><a target="_blank" href="https://docs.google.com/document/d/1LTTfS3iGPjMAc4AlVSuWEC7eVXkOqDE_a141x14AMhg/pub">Future Plans</a></h2>
