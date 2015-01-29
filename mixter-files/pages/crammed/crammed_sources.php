<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

?><style >
	.pagehead {
	  color: #000 !important;
	  font-size: 27px !important;
	}
	
	.artist {
		padding: 5px;
		padding-left: 10px;
		margin: 10px;
		border: 1px solid #84AE1F;
		min-width: 500px;
	}
	.artist img {
		padding: 0;
		margin: 0;
		border: none;
		float: right;
	}
</style>
<div style="width:65%">
<h1  class="pagehead">Crammed Discs Remix Sources</h1>
<div  class="artist">
<img  src="<?= $T->URL('crammed_small_cibelle.jpg'); ?>" alt="Cibelle" width="280" height="60" />
<p ><a  href="<?= $A['root-url']?>files/cibelle/4893">"Noite de Carnaval" (A Capellas)</a> by Cibelle</p>
<p ><a  href="<?= $A['root-url']?>files/cibelle/4892">"Noite de Carnaval" (Loops)</a> by Cibelle</p>
</div>
<div  class="artist">
<img  src="<?= $T->URL('crammed_small_apollo.jpg'); ?>" alt="Apollo Nove" width="280" height="60" />
<p ><a  href="<?= $A['root-url']?>files/apollonove/4904">"Yage Cameras" (Loops)</a> by Apollo Nove</p>
<p ><a  href="<?= $A['root-url']?>files/apollonove/4903">"Yage Cameras" (Tracks)</a> by Apollo Nove</p>
</div>
<div  class="artist">
<img  src="<?= $T->URL('crammed_small_djd.jpg'); ?>" alt="DJ Dolores" width="280" height="60" />
<p ><a  href="<?= $A['root-url']?>files/djdolores/4889">"Sanidade" (A Capellas)</a> by DJ Dolores</p>
<p ><a  href="<?= $A['root-url']?>files/djdolores/4888">"Sanidade" (Loops)</a> by DJ Dolores</p>
</div>
</div>
