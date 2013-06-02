<?php   defined('C5_EXECUTE') or die("Access Denied."); ?>
<div class="clear"></div>
	
	<div id="footer">
	
		<div id="footer-inner">
		
			<p class="footer-sign-in">
			<?php  
			$u = new User();
			if ($u->isRegistered()) { ?>
				<?php   
				if (Config::get("ENABLE_USER_PROFILES")) {
					$userName = '<a href="' . $this->url('/profile') . '">' . $u->getUserName() . '</a>';
				} else {
					$userName = $u->getUserName();
				}
				?>
				<span class="sign-in"><?php  echo t($userName)?></span>
			<?php   } else { ?>

<a href="http://www.facebook.com/pages/Bizztemp/386059295580" target="_blank"><img src="http://www.bizztemp.nl/img/facebook_001.jpg" alt="facebook" height="64"></a>
<a href="https://twitter.com/#!/Bizztemp"><img src="http://www.bizztemp.nl/img/Twitter_001.jpg" alt="twitter" height="64"></a>
<a href="http://nl.linkedin.com/pub/bizztemp-interim-management/1b/18b/4a6"><img src="http://www.bizztemp.nl/img/Linkedin_001.jpg" alt="linkedin" height="64"></a>
<a href="http://maps.google.com/maps/place?cid=18331160403346167493&amp;q=Tappersweg+8-k++2031+ET+Haarlem&amp;hl=en&amp;t=m&amp;dtab=2&amp;ie=UTF8&amp;ll=52.40238,4.648333&amp;spn=0.000013,0.000021&amp;z=16&amp;vpsrc=0" target="_blank"><img src="http://www.bizztemp.nl/beta/webroot/img/map.png" alt="map" height="64"></a>
<img src="http://www.bizztemp.nl/img/blanc.jpg" alt="pending" height="64">
<a href="http://www.normeringarbeid.nl/" target="_blank"><img src="http://www.bizztemp.nl/img/cer_norm.png" alt="de norm" height="64"></a>
<a href="http://www.vro.net/" target="_blank"><img src="http://www.bizztemp.nl/img/cer_vro.png" alt="vro" height="64"></a>
<a href="http://www.vca.nl/voor-uitzendorganisaties/wat-is-vcu.aspx" target="_blank"><img src="http://www.bizztemp.nl/img/cer_vcu.png" alt="vcu" height="64"></a>
<a href="http://www.kiesriv.nl/cms/publish/content/showpage.asp?themeid=3" target="_blank"><img src="http://www.bizztemp.nl/img/cer_riv.png" alt="riv" height="64"></a>


			<?php   } ?>
			</p>
			
			<div class="clear"></div>
			<p class="footer-copyright"> <?php  echo SITE?>. &copy; <?php  echo date('Y')?> Alle rechten voorbehouden. Ingeschreven op KVK Haarlem. KVK nr: 881 8428 87</p>
			<p class="footer-tag-line"><?php echo t('')?></p>
	
		</div>
	
	</div>

<!-- end main container -->

</div>

<?php   Loader::element('footer_required'); ?>

</body>
</html>
