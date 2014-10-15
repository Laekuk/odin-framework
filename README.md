Odin-Framework
==============

This is lightweight (simplistic) php framework that uses a hybrid both Functional and Object Oriented programming styles.
The goal is to have a tiny footprint with no server modifications.

<h2>Setup</h2>
<ol>
	<li>Download the codebase and place the "odin" folder somewhere in your website</li>
	<li>From any file require_once() the "fury.php" file, which is located in the "odin"" folder</li>
	<li>You now have access to the <strong>$odin</strong> variable and may use it to call any method in any of the bolts (libraries)</li>
</ol>

<h2>Example</h2>
<strong>$odin</strong>->array->overwrite_merge_recursive($arrayA,$arrayB);