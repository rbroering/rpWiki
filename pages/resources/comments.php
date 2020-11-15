<?php

class Comment {
	
}

class comments extends PageElementBase {
	private $Comments;
	public $Extension	= [];
	public $Data		= [];

	public function __construct( $Page ) {
		global $GlobalImport;
		extract( $GlobalImport );

		if (is_string( $Page ))
			$this->Extension['page'] = $Page;
		elseIf (is_array( $Page )) {
			$this->Extension['page'] = $Page['page'];
		}

		$this->Extension['show']['title'] = (isset( $Page['show']['title'] ) && !$Page['show']['title']) ? false : true;

		if (p('comments-view-hidden'))
			$this->Comments = $dbc->prepare( "SELECT id FROM comments WHERE page = :page AND type != 'reply'" );
		else
			$this->Comments = $dbc->prepare( "SELECT id FROM comments WHERE page = :page AND type != 'reply' AND hidden != 1" );

		$this->Comments->execute([
			':page'	=> $this->Extension['page']
		]);
		$this->Data['count'] = $this->Comments->rowCount();
	}

	private function __insertLine() {
		?>
		<div id="commentsectionheader" onclick="commentsToggle();" >
			<h2 class="sectiontitle" >
				<?php msg( 'sectionheading-comm' ); ?>
				<span class="num" ><?php echo $this->Data['count']; ?></span>
				<span class="commentsToggle commentsToggleOpen" ><?php msg( 'comm-showcomments' ); ?></span>
				<span class="commentsToggle commentsToggleClose hide" ><?php msg( 'comm-hidecomments' ); ?></span>
			</h2>
		</div>
		<?php
	}

	private function __insertForm() {
		global $GlobalImport;
		extract( $GlobalImport );

		if (p( 'writecomments' )) {
		?>
<input type="hidden" id="e_Comments_Page" value="<?php echo $this->Extension['page']; ?>" />
<div id="e__Comments_Write" >
	<form id="e__Comments_Form" method="post" >
		<input type="text" id="e__Comments_Title" name="comment_title" class="big-input" placeholder="<?php msg( 'comm-ph-title' ); ?>" />
		<br />
		<textarea id="e__Comments_Content" name="comment_content" class="top10 big-textarea message" placeholder="<?php msg( 'comm-ph-content' ); ?>" ></textarea>
		<br />
		<input type="submit" id="e__Comments_Submit" value="<?php msg( 'comm-send' ); ?>" class="top10" />
	</form>
</div>
		<?php
			}
		?>
<div id="e__Comments_Timeline"<?php if (!p( 'writecomments' ) && $this->Data['count'] === 0) { echo ' style="display: none;"'; } ?> >
	<?php
		$sql__Limit = 10;
		$Comments = $dbc->prepare( "SELECT * FROM comments WHERE page = :page AND type != 'reply' ORDER BY timestamp DESC LIMIT $sql__Limit" );
		$Comments->execute([
			':page'	=> $this->Extension['page']
		]);
		$Comments = $Comments->fetchAll();

		foreach ($Comments as $Comment) {
			$writer = $dbc->prepare( "SELECT username, usericon FROM user WHERE username = :username LIMIT 1" );
			$writer->execute( [':username' => $Comment['writer']] );
			$writer = $writer->fetch();

			if (!$Comment['hidden'] || p( 'hidecomments', $Comment['writer'] )) {
			?>
<a name="c-<?php echo $Comment['rid']; ?>" ></a>
<div class="commentfield<?php if ($Comment['hidden']) { echo ' hidden-comment'; } ?>" data-id="<?php echo $Comment['rid']; ?>" >
	<a href="<?php echo fl( 'user', ['?' => $writer['username']] ); ?>" title="<?php echo $writer['username']; ?>" >
		<div class="commentavatar" style="background: url('<?php echo get_usericon($writer['username']); ?>');" onmouseover="commentTime(1, 'cT__<?php echo $Comment['rid']; ?>')" onmouseout="commentTime(0, 'cT__<?php echo $Comment['rid']; ?>')" >
		</div>
		<div class="commentusername" ><?php echo $writer['username']; ?></div>
	</a>
	<div id="cT__<?php echo $Comment['rid']; ?>" class="commenttime" ><?php timestamp( $Comment['timestamp'] ); ?></div>
	<div class="comment" data-writer="<?php echo $Comment['writer']; ?>" data-writer-group="<?php #echo (p($writer['rights'], 'allrights')) ? 'highlight' : null; ?>" >
		<div class="commentarrow arrow" ></div>
		<div class="commentcontent" >
		  <div id="cc<?php echo $Comment['rid']; ?>" >
			<?php if (!empty( $Comment['title'] )) { ?><div class="commenttitle title bw" ><?php echo $Comment['title']; ?></div><?php } ?>
			<div class="commenttext bw<?php if (empty( $Comment['title'] )) { echo ' notitle'; } ?>" >
				<?php echo $Comment['content']; ?>
			</div>
		  </div>
			<?php
				if (p( 'editcomments', $Comment['writer'] )) {
			?>
				<div id="ce<?php echo $Comment['rid']; ?>" class="commenteditor" >
					<form method="post" class="e__Comments_Edit_Form commenteditform" data-cID="<?php echo $Comment['rid'];  ?>" >
						<input type="text" name="cTitle" class="cInput cTitle cTitle<?php echo $Comment['rid']; ?>" maxlength="150" autocomplete="off" placeholder="<?php msg('comm-edit-ph-title', 0) ?>" value="<?php echo $Comment['title']; ?>" /><br />
						<textarea type="text" name="cContent" class="cTextarea cContent cContent<?php echo $Comment['rid']; ?>" placeholder="<?php $nsUser = 0; if($nsUser == 1) { msg('up-comm-ph-content'); } else { msg('comm-ph-content'); } ?>" ><?php echo $Comment['content']; ?></textarea>
						<input type="submit" class="e__Comments_Edit_Submit cInput cSubmit cSubmit<?php echo $Comment['rid']; ?>" value="<?php msg('comm-edit-send') ?>" />
					</form>
					<div class="commenteditlink" >
						<span class="activateCommentEditor deactivateCommentEditor editlink" onclick="deactivateCommentForm('#ceL<?php echo $Comment['rid']; ?>', '#cc<?php echo $Comment['rid']; ?>', 'ce<?php echo $Comment['rid']; ?>');" ><?php msg('comm-edit-quit') ?></span>
					</div>
				</div>
			<?php
				}
			?>
		</div>
			<?php
				if (!empty( $User )) {
			?>
			<div id="ceL<?php echo $Comment['rid']; ?>" class="commentedit" >
				<div class="commentreply" >
					<span id="crL<?php echo $Comment['rid']; ?>" class="activateReplyEditor replylink" onclick="activateReplyForm('#cr<?php echo $Comment['rid']; ?>', 'crL<?php echo $Comment['rid']; ?>')" ><?php msg('comm-reply') ?></span>
				</div>
			<?php
				if (p( 'editcomments', $writer['username'] )) {
			?>
				<div class="commenteditlink" >
					<span class="activateCommentEditor editlink" onclick="activateCommentForm('#ce<?php echo $Comment['rid']; ?>', 'cc<?php echo $Comment['rid']; ?>', 'ceL<?php echo $Comment['rid']; ?>');" ><?php msg('comm-edit') ?></span>
				</div>
				<div class="commentdeletelink" >
					<?php
						if (p( 'deletecomments', $Comment['writer'] )) {
							?>
								<form method="post" class="e__Comment_Delete_Form" style="display: inline-block;" >
									<input type="hidden" name="cAction" value="delete" />
									<input type="hidden" name="cRId" value="<?php echo $Comment['rid']; ?>" />
									<input type="submit" id="deleteSubmit_<?php echo $Comment['rid']; ?>" />
									<label for="deleteSubmit_<?php echo $Comment['rid']; ?>" class="e__Comment_Delete_Button deletelink editlink" ><?php msg('comm-delete') ?></label>
								</form>
							<?php
						}
						if (p( 'hidecomments', $Comment['writer'] )) {
							?>
								<form method="post" class="e__Comment_Hide_Form" style="display: inline-block;" >
									<input type="hidden" name="cAction" value="hide" />
									<input type="hidden" name="cRId" value="<?php echo $Comment['rid']; ?>" />
									<input type="submit" id="hideSubmit_<?php echo $Comment['rid']; ?>" />
									<label for="hideSubmit_<?php echo $Comment['rid']; ?>" class="e__Comment_Hide_Button deletelink editlink" >
										<span class="e__Comment_Hide_Label<?php if ($Comment['hidden']) { echo ' hide'; } ?>" ><?php msg( 'comm-hide' ); ?></span>
										<span class="e__Comment_Unhide_Label<?php if (!$Comment['hidden']) { echo ' hide'; } ?>" ><?php msg( 'comm-unhide' ); ?></span>
									</label>
								</form>
							<?php
						}
					?>
				</div>
			<?php
				}
			?>
			</div>
			<?php
				}
			?>
	</div>
	<div class="replyfield" >
		<div id="cr<?php echo $Comment['rid']; ?>" class="writereply" >
			<form method="post" >
				<input type="hidden" name="cAction" value="replynew" />
				<input type="hidden" name="cRId" value="<?php echo $Comment['rid']; ?>" />
				<textarea type="text" name="cContent" class="e__Replies_Content rTextarea rContent" placeholder="<?php msg('reply-ph-content', 0) ?>" onclick="cRClicked();" onblur="cROnblur();" ></textarea><br />
				<input type="submit" class="e__Replies_Submit rInput rSubmit bluesubmit" value="<?php msg('reply-send', 0) ?>" />
			</form>
		</div>
		<div class="commentreplies" >
			<?php
				$e__Replies_Limit = 80;
				$replies = $dbc->prepare("SELECT * FROM comments WHERE type = 'reply' AND toRid = :toRid ORDER BY timestamp LIMIT $e__Replies_Limit");
				$replies->execute([ ':toRid' => $Comment['rid'] ]);
				$repliesNum = $replies->rowCount();

			if ($repliesNum > 0) {
					?>
						<div class="replyfieldborder" >
							<div class="replyfieldarrow" ></div>
							<small class="replynumber" ><?php msg('comm-reply-nr', 0, $repliesNum) ?></small>
						</div>
					<?php
				$replies = $replies->fetchAll();

				foreach ($replies as $Reply) {
					if (!$Reply['hidden'] || p( 'hidereplies', $Reply['writer'] )) {

					$writer = $dbc->prepare("SELECT usericon FROM user WHERE username = :username LIMIT 1");
					$writer->execute([
						':username' => $Reply['writer']
					]);
					$writer = $writer->fetch();
					?>
					<a name="r-<?php echo $Reply['rid']; ?>" ></a>
					<div class="reply<?php echo ($Reply['hidden']) ? ' hidden-comment' : ''; ?>" data-id="<?php echo $Reply['rid']; ?>" data-writer="<?php echo $Reply['writer']; ?>" >
						<a href="<?php echo fl( 'user', ['?' => $Reply['writer']] ); ?>" title="<?php echo $Reply['writer']; ?>" >
							<div class="replyavatar" style="background: url('<?php echo get_usericon($Reply['writer']); ?>');" >
								<img src="<?php echo get_usericon($Reply['writer']); ?>" height="60px" width="60px" />
							</div>
						</a>
						<div id="cc<?php echo $Reply['rid']; ?>" class="replycontent bw" title="<?php timestamp($Reply['timestamp']); ?>" onclick="activateReplyEdit('.del<?php echo $Reply['rid']; ?>', '.ceL<?php echo $Reply['rid']; ?>')" >
							<?php echo $Reply['content']; ?>
						</div>
						<?php
						if (p( 'editreplies', $Reply['writer'] )) {
						?>
						<div id="ce<?php echo $Reply['rid']; ?>" class="commenteditor replyeditor" >
							<form method="post" class="e__Reply_Edit_Form commenteditform" >
								<input type="hidden" name="rAction" class="cAction<?php echo $Reply['rid']; ?>" value="edit" />
								<input type="hidden" name="cRId" value="<?php echo $Reply['rid']; ?>" />
								<textarea type="text" name="cContent" class="cTextarea cContent cContent<?php echo $Reply['rid']; ?>" placeholder="<?php msg( 'reply-ph-content' ); ?>" ><?php echo $Reply['content']; ?></textarea>
								<input type="submit" id="write_cSubmit" class="e__Reply_Edit_Submit cInput rSubmit cSubmit<?php echo $Reply['rid']; ?>" value="<?php msg( 'reply-edit-send' ); ?>" />
							</form>
							<div class="commenteditlink" >
								<span class="activateCommentEditor deactivateCommentEditor editlink" onclick="deactivateCommentForm('#ceL<?php echo $Reply['rid']; ?>', '#cc<?php echo $Reply['rid']; ?>', 'ce<?php echo $Reply['rid']; ?>');" ><?php msg( 'comm-edit-quit' ); ?></span>
							</div>
						</div>

						<div id="ceL<?php echo $Reply['rid']; ?>" class="commenteditlink replyeditdeletelinks ceL<?php echo $Reply['rid']; ?>" >
								<span class="activateCommentEditor editlink" onclick="activateCommentForm('#ce<?php echo $Reply['rid']; ?>', 'cc<?php echo $Reply['rid']; ?>', 'ceL<?php echo $Reply['rid']; ?>'); $('#del<?php echo $Reply['rid']; ?>').css('display', 'none');" ><small><?php msg( 'comm-edit' ); ?></small></span>
							</div>
						<div id="del<?php echo $Reply['rid']; ?>" class="commentdeletelink replyeditdeletelinks del<?php echo $Reply['rid']; ?>" >
							<?php
							if (p( 'deletereplies', $Reply['writer'] )) {
								?>
							<form method="post" class="e__Reply_Delete_Form" style="display: inline-block;" >
								<input type="hidden" name="rAction" value="delete" />
								<input type="hidden" name="cRId" value="<?php echo $Reply['rid']; ?>" />
								<input type="submit" id="deleteSubmit<?php echo $Reply['rid']; ?>" />
								<label for="deleteSubmit<?php echo $Reply['rid']; ?>" class="e__Reply_Delete_Button deletelink editlink" ><small><?php msg( 'comm-delete' ); ?></small></label>
							</form>
							<?php
							}
							?>
							<form method="post" class="e__Reply_Hide_Form" style="display: inline-block;" >
								<input type="hidden" name="rAction" value="hide" />
								<input type="hidden" name="cRId" value="<?php echo $Reply['rid']; ?>" />
								<input type="submit" class="" id="hideSubmit<?php echo $Reply['rid']; ?>" />
								<label for="hideSubmit<?php echo $Reply['rid']; ?>" class="e__Reply_Hide_Button deletelink editlink" >
									<small class="e__Comment_Hide_Label<?php if ($Reply['hidden']) { echo ' hide'; } ?>" ><?php msg( 'comm-hide' ); ?></small>
									<small class="e__Comment_Unhide_Label<?php if (!$Reply['hidden']) { echo ' hide'; } ?>" ><?php msg( 'comm-unhide' ); ?></small>
								</label>
							</form>
						</div>
						<?php
						}
						?>
					</div>
					<?php
					}
				}
			}
			?>
		</div>
	</div>
</div>
			<?php
			}
		}
	?>
</div>
		<?php
	}

	protected function __fetchComments() {
		if (key_exists( 'comm', $this->Extension )) {
			$Extension = $this->Extension['log'];
		}
	}

	function insert() {
		if ($this->Extension['show']['title']) {
			$this->__insertLine();
		}
		$this->__insertForm();
		$this->__fetchComments();
	}
}