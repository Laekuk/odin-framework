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

<h2>Examples</h2>
<h3>Calling a class (bolt) method</h3>
<strong>$odin</strong>->array->overwrite_merge_recursive($arrayA,$arrayB);
<ul>
	<li><strong>array</strong> is the class (bolt) that contains the method we are calling</li>
	<li><strong>overwrite_merge_recursive</strong> is the method inside of the array class that we are calling</li>
</ul>

<h3>Calling a Brick-Method</h3>
<p>A Brick is a set of code that accomplishes a task all on its own. They are not intended to be entire web-apps all on their own. As a rule of thumb, bricks can use bolts, but bolts should never call bricks.</p><p>The example code below is initializing Rune, a Micro-CMS that is baked into this library.</p>

<strong>$odin</strong>->brick->rune_micro_cms->admin_panel();
<ul>
	<li><strong>brick</strong> is the bolt we use to load bricks</li>
	<li><strong>rune_micro_cms</strong> is the name of the class (brick) that contains the method we are calling</li>
	<li><strong>admin_panel</strong> is the method inside of our brick we are calling</li>
</ul>
