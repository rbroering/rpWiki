<?php

class Page extends PageBase {
	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return 'Information';
			break;
		}
	}

	public function insert() {
		$show = 'c';
		if (isset( $_GET['tou'] )) {
			$show = 'tou';
		} elseIf (isset( $_GET['pp'] )) {
			$show = 'pp';
		}
	?>
	<div class="btm30" align="center" >
		<a href="info?c" >Content</a> &bull; <a href="info?tou" >Terms of use</a> &bull; <a href="info?cc" >Code of conduct</a> &bull; <a href="info?pp" >Privacy Policy</a>
	</div>
	<?php
		if($show == 'c') {
	?>

	<h2 class="sectiontitle" >Information about content on this website <small>05/22/18</small></h2>

	<p>
	DISCLAIMER: RuvenProductions reserves the right to change the license and the rules at any time without a notification.
	</p>
	<p>
	Content and media of every type that is posted on this website (“RuvenProductions”) by registered users is published and released under the CC-BY-NC-SA 4.0 license, if no other license is clearly given for the media. More information on the Creative Commons license can be found on <a href="https://creativecommons.org/licenses/by-nc-sa/3.0/" >https://creativecommons.org/licenses/by-nc-sa/4.0/</a>. The complete legal code is available on <a href="https://creativecommons.org/licenses/by-nc-sa/3.0/legalcode" >https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode</a>. You may share content and alter it for non-commercial purposes (NC), but are required to name and link to the source (BY) and share your version under the same license (SA). You can reach out to most users on the message wall on their profiles.
	</p>
	<p>
	RuvenProductions is not liable for any media and information that is uploaded, posted or in other ways published by either unregistered/anonymous users or registered users on its website. Users are liable for the content, media and data which they publish on this website, including copyright infringements and any violations against the law. RuvenProductions reserves themselves and their users the right to alter content on this website at any time without the neccessity of an approval by the original creator or contacting the creator, under the requirement that the new content is compatible with RuvenProductions's <a href="?c#rules" >content rules</a>. Content can also be hidden from users for an unlimited amount of time or removed completely without the need of an explanation. Any user who violates the content rules can be banned, hidden and deleted immediately.
	</p>
	<p>
	RuvenProductions reserves the right to promote content released by its users in other places of this website or in social media.
	</p>
	<a name="rules" ></a>
	<h3 class="sectiontitle" >Content Rules</h3>
	<p>
	<ol>
		<li>It is forbidden to release copyrighted content on RuvenProductions without having a true agreement by the original owner of that content. You must be able to provide the clear agreement at any time.</li>
		<li>It is strictly forbidden to release content that could possibly harm others physically or psychically or mentally. The attempt is strictly forbidden.</li>
		<li>It is forbidden to release content that violates the laws of your country.</li>
		<li>It is forbidden to use terms that are considered to be unpolite or vulgar in a text or image.</li>
		<li>It is strictly forbidden to attempt to restrict an user's opinion or the right to freely share one's opinion. Exceptions can be possible if the opinion violates the law.</li>
		<li>Posting a call / an appeal for cyber crimes is strictly forbidden.</li>
		<li>Posting a call / an appeal for crimes in the real world is strictly forbidden.</li>
		<li>It is prohibited to try to use malfunctionings of the website or trying to hack it by posting harmful content.</li>
		<li>It is prohibited to spam / posting the same content multiple times.</li>
		<li>It is forbidden to publish content that could annoy other users.</li>
		<li>It is prohibited to advertise any product or service without RuvenProductions's agreement.</li>
	</ol>
	</p>

	<?php
		} elseIf ($show == 'tou') {
			/* ensure... */
	?>

	<h2 class="sectiontitle" >Terms of use <small>05/22/18</small></h2>

	<p style="max-width: 600px;" >
	Read this text carefully!
	<p>
	RuvenProductions reserves the right to change the terms of use at any time without a notification.
	</p>
	By creating an account on this website (“RuvenProductions”), the user (“you”) agrees to the terms of use.
	<br />
	<ol>
		<li>By agreeing to the terms of use, you also confirm that you have read and understood the <a href="?cc" >code of conduct</a> and agree to it.</li>
		<li>By agreeing to the terms of use, you also confirm that you have read and understood the <a href="?c" >information on content on this website</a> and agree to it.</li>
		<li>By agreeing to the terms of use, you also confirm that you have read and understood the <a href="?pp" >Privacy Policy</a> and agree to it.</li>
		<li>You are not allowed to alter (“hack”) the functionality of this website or RuvenProductions's other services. The attempt to archieve malfunctioning of this website by changing its code or using forms to overload the server with requests is strictly forbidden.</li>
		<li>You ensure that anything you post, upload or in any other way publish on this website is in no way dangerous or harmful to the users of this website. The attempt to hurt an user physically or psychically or mentally is strictly forbidden.</li>
		<li>By creating an account on RuvenProductions, you confirm that your registration is in consent with the laws of the nation you live in. RuvenProductions is not liable for any violation of the laws that apply to the users.</li>
		<li>Your experiences/abilities with this website can be restricted if you violate the terms of use or disturb other users or are noticed negatively.</li>
	</ol>
	</p>

	<?php
		} elseIf ($show == 'pp') {
	?>

	<h2 class="sectiontitle" >Privacy Policy <small>05/22/18</small></h2>

	<p>
	RuvenProductions reserves the right to change the privacy policy at any time without a notification.
	</p>

	<p>
	You can see your information on <a href="user?myprofile" >your profile page</a>.
	</p>
	<p>
	RuvenProductions and its services do not collect any information to use it for advertising and do not sell or give any information to advertisers. RuvenProductions and its services do not support website tracking.
	</p>
	<p>
	This website collects following information about you:
	<ol>
		<li>Your username and password,</li>
		<li>your profile image / user icon,</li>
		<li>your user group rights / permissions and restrictions,</li>
		<li>your <a href="preferences" >user preferences</a>,</li>
		<li>the amount of notifications you have, the amount of changes in each category (page types and change types) you made,</li>
		<li>every contribution and edit you made to pages (and your blogs), including your personal <a href="editor?css" >CSS</a> (for the website's design) and <a href="editor?js" >JS</a> (for the website's functions), which you can freely customize,</li>
		<li>every message you send to other users and every comment you write,</li>
		<li>every RuvenProductions service that you use.</li>
	</ol>
	</p>
	<p>
	RuvenProductions's website does currently not use cookies. In case that it will start to use cookies, you will be notified. Other RuvenProductions services might already use cookies. These include:
	<ol>
	<li><a href="apps/Chims Messenger/" >Chims Messenger</a></li>
	</ol>
	</p>
	<p>
	If you want information about you or others whom you do know to be deleted, please <a href="user?user=RuvenProductions&p=msg#write" >contact RuvenProductions</a> or <a href="user?user=Ruven&p=msg#write" >Ruven</a> (you must be logged in). You do not need to give a reason if you want your own data to be deleted.
	</p>
	<?php
		}
	}
}
?>