<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

?><style >
#inner_content {
    width: 80%;
    margin: 0px auto;
}


h1.pagehead {
  color: #000 !important;
  font-size: 27px !important;
}
  	
p { 
  font-size: 12px; 
}

.contestbox {
	background-color:#fff;
  margin-bottom:0px;
	width: 320px;
	float: right;
}

.contestback {
  display: block;
	margin: 10px;
}

.contestback img {
	padding: 0;
	margin: 0;
	margin-bottom: -2px;
}

#photocr p {
	font-size: 0.9em;
}

.pullquote {
	margin: 0px 1%;
	border-top: 2px solid #bbb;
	border-bottom: 1px solid #ccc;
	padding: 10px 1%;
	color: #333;
	font-size: 0.99em;
	width: 30%;
	/* float: left; */
	height: 220px;
	line-height: 1.4em;
    text-align: right;
}
.pullquote:hover {
	border-top: 2px solid #99b;
	border-bottom: 1px solid #88c;
}
.pullquote h1 {
	font-weight: normal;
	color: #eee;
	letter-spacing: 25%;
	padding:0;
	margin: 0;
	border-bottom: none !important;
	display: none;
}
</style>
<h1 class="pagehead">ccMixter &amp; Crammed Discs</h1>
<img  src="<?= $T->URL('crammed-logo.png'); ?>"
     style="float: left; margin-right: 10px; margin-bottom: 5px; margin-top:20px" height="85"
    alt="Crammed" title="Crammed" />
<div  class="contestbox">
    <div  class="contestback">
        <img  src="<?= $T->URL('crammed_comp_cibelle.jpg'); ?>" alt="Crammed - Cibelle" /><img  src="<?= $T->URL('crammed_comp_apollo.jpg'); ?>" alt="Crammed - Apollo Nove" /><img  src="<?= $T->URL('crammed_comp_djd.jpg'); ?>" alt="Crammed - DJ Delores" />
        <div  style="padding: 0pt 0px 10px; text-align: left; line-height: 1.4em; " id="photocr">
            <p >Cibelle photo &copy; Kevin Westenberg / Crammed Discs.
                Apollo Nove photo &copy; Raphael Gianelli&mdash;Meriano.
                DJ Dolores photo &copy; Renato Filho / Crammed Discs.
            </p>
            <p >All photos used with permission.</p>
        </div>
    </div>
</div>
<div style="height:150px">
    <p><a href="http://creativecommons.org/">Creative Commons</a> and <a  href="http://www.crammed.be/">Crammed Discs</a>
    are pleased to anncounce Crammed Discs music in the Commons. Crammed artists Cibelle, DJ Dolores, and Apollo Nove &mdash; some of
    Brazil's most creative musical innovators &mdash; are offering new music online under a <a
    href="http://creativecommons.org/licenses/by-nc/2.5/">CC BY-NC 2.5 license</a>, so that producers worldwide
    can use the tracks in remixes and new compositions.</p>
    <p style="font-size:18px"><a href="<?= $A['root-url']?>crammed_sources">Download audio sources here</a></p>
</div>
<p style="text-align:right">
    <div class="pullquote"><h1 >Cibelle</h1>"The whole process of making music has changed. The very concept of composition now extends to the creation of sounds and textures. I'm very curious to see how other people will use and manipulate my sounds and how they will use them as tools to create new music." <br  />&mdash; <strong >Cibelle</strong></div>
    <div class="pullquote"><h1 >Apollo Nove</h1>"I like the idea of giving people the opportunity to hear what I hear when I'm producing &mdash; a separate candombl&eacute; percussion track or some painstakingly constructed soundscape. If mixing is part of the compositional process, it's only natural that I try sharing the compositional responsibilities with anyone interested in taking them on." <br  />&mdash; <strong >Apollo Nove</strong></div>
    <div class="pullquote" style="margin-right: 0; padding-right:0;"><h1 >DJ Dolores</h1>"This is what every intelligent musician should do. The idea is to share and allow one's work to be cut up, reinvented and &mdash; who knows &mdash; transformed into something even better than the original. This isn't about generosity; it's about inventing new ways of creating musical products that go well beyond the world of physical carriers like vinyl and CDs." <br  />&mdash; <strong >DJ Dolores</strong></div>
</p>
