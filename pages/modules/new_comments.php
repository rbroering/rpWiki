<?php

require_once("replies_list.php");

/**
 * Represents a single comment which
 * can be requested by providing its
 * random ID.
 */
class Comment {
	private $id, $Page, $PageId;
	private $exists = false;
	private $Data;
	private $Replies = null;
	private $Author;

	/**
	 * Loads all data and initializes the object.
	 *
	 * @param String $id
	 */
    public function __construct(String $pageId, String $id) {
		global $GlobalImport;
		extract($GlobalImport);

		$Comment = $dbc->prepare("SELECT * FROM comments WHERE rid = :id AND type != 'reply' LIMIT 1");
		$Comment->execute([
			':id'	=> $id
		]);
		$Comment = $Comment->fetch();

		$Page = $dbc->prepare("SELECT id, `url` FROM pages WHERE `url` = :pageId LIMIT 1");
		$Page->execute([
			':pageId'	=> $pageId
		]);
		$Page = $Page->fetch();

		if ($Comment) {
			$this->id		= $id;
			$this->PageId	= $pageId;
			$this->Page		= $Page ? $Page['url'] : "";
			$this->exists	= true;
			$this->Data		= $Comment;

			$this->Author = new User();
			$this->Author->setUserByName($Comment['writer']);

			// Load replies
			$this->Replies = new RepliesList($pageId, $id);
		}
	}

	/**
	 * Returns a boolean indicating whether the queried
	 * comment exists.
	 *
	 * @return boolean
	 */
	public function exists() : bool {
		return $this->exists;
	}

	/**
	 * Returns a boolean indicating whether there are
	 * replies in context to this comment.
	 *
	 * @return boolean
	 */
	public function hasReplies() : bool {
		return empty($this->Replies);
	}

	/**
	 * Prints the replies that are in context to this
	 * comment.
	 *
	 * @return void
	 */
	public function insertReplies() : void {
		if ($this->Replies->countReplies() > 0) $this->Replies->insert();
	}

	/**
	 * Returns an array with the name of the user who made
	 * the latest change to the comment's content and the
	 * timestamp of the change, or null if it has not been
	 * changed.
	 *
	 * @return object|null
	 */
	public function getLatestEdit() {
		global $dbc;

		$Log = $dbc->prepare("SELECT * FROM log WHERE pageURL = :pageAddress AND page2 = :commentRandId AND type = 'comment-edit' ORDER BY timestamp DESC LIMIT 1");
		$Log->execute([
			':pageAddress'		=> $this->PageId,
			':commentRandId'	=> $this->id
		]);
		$Log = $Log->fetch();

		if (!$Log) return null;

		return [
			'user'		=> $Log['user'],
			'username'	=> $Log['username'],
			'timestamp'	=> $Log['timestamp'],
			'timezone'	=> $Log['timezone']
		];
	}

	/**
	 * Prints the comment
	 *
	 * @return 
	 */
	public function insert() {
		if (!$this->exists) return;

		global $Wiki, $Actor, $dbc;

		$HTML = new HTMLTags();
		$HTML->setPrintMode(true);
		$HTML->setAutoIndent(true);
		$HTML->setAutoNl(true);

		$idRand		= $this->id;
		$idNinc		= $this->Data['id'];
		$Author		= $this->Author->getName();
		$AuthorID	= $this->Author->getRandId();
		$Timestamp	= timestamp($this->Data['timestamp'], 1);

		$Hidden		= $this->Data['hidden'];

		$Title		= (!empty($this->Data['title'])) ? $this->Data['title'] : false;
		$Content	= $this->Data['content'];

		$Usericon	= $this->Author->getIcon([200, 200], 'cssurl');

		$UserTag	= [];
		$GroupTags	= [
			'allrights'	=> [],
			'staff'		=> [],
			'helper'	=> [],
			'admin'		=> []
		];

		foreach ($Wiki['groups'] as $Groupname => $GroupData) {
			if (array_key_exists($Groupname, $GroupTags) && ur($Groupname, $Author)) {
				$UserTag = array_merge([
					'group' => $Groupname,
					'label' => msg("group-$Groupname", 1),
					'class'	=> ["userright-$Groupname"]
				], $GroupTags[$Groupname]);
				break;
			}
		}

		/* HTML */

		$HTML->open("div_comment_$idRand", 'div', [
			'id'	=> "comment_$idRand",
			'class'	=>
				$HTML->class([
					'comment',
					($Hidden) ? 'hidden-comment' : ''
				])
		]);

		$HTML->jumpAnchor("c-$idRand");

		/* HIDDEN COMMENT DETAILS */
		if ($this->Data['hidden']) {
			if (!$Actor->hasPermission('comments-view-hidden')) return false;

			$Log = $dbc->prepare(
				"SELECT user, username, timestamp, timezone FROM log WHERE type = 'comment-hide' AND page = :pageURL AND page2 = :randId ORDER BY timestamp DESC LIMIT 1"
			);
			$Log->execute([
				':pageURL'	=> $this->PageId,
				':randId'	=> $this->id
			]);
			$Log = $Log->fetch();

			$HTML->divClass(['comment_hidden_info'], msg('module-comments-hidden-by', 1, $Log ? [$Log['username'], timestamp($Log['timestamp'], 1)] : []));
		}
		/* hidden comment details */

		/* USERICON */
		$HTML->open("div_comment_userinfo_$idRand", 'div', [
			'class'	=> $HTML->class(['userinfo'])
		]);
		$HTML->setPrintMode(false);
		echo $HTML->a(
			fl('user', ['?' => $Author]),
			['title' => $Author],

			$HTML->tag('div', [
				'class' => $HTML->class(['usericon']),
				'style'	=> $HTML->style([
					'background-image' => $Usericon
				])
			])
		);

		if (!empty($UserTag)) {
			if (!array_key_exists('class', $UserTag) || !is_array($UserTag['class']))
				$class = $UserTag['class'] = [];

			echo $HTML->tag('div', [
				'class'	=> $HTML->class(array_merge(['usertag'], $UserTag['class'])),
				//'style' => $HTML->style([])
			], $UserTag['label']);
		}

		$HTML->setPrintMode(true);
		$HTML->close("div_comment_userinfo_$idRand");
		/* usericon */

		/* BODY */
		$HTML->open("div_comment_body_$idRand", 'div', [
			'class'				=> $HTML->class(['comment_body']),
			'data-page-address'	=> $this->Page,
			'data-comment-id'	=> $idRand,
			'data-writer'		=> $Author,
			'data-author-groups'=> $this->Author->listGroupsInString(',', true)
		]);

		/* CONTENT */
		$classes = ['comment_content'];
		if (!$Title) $classes[] = 'comment_untitled';

		$HTML->open("div_comment_content_$idRand", 'div', [
			'class'	=> $HTML->class($classes)
		]);

		/* TEXT */
		$classes = $Title ? ['comment_title', 'title', 'bw'] : ['comment_title', 'title', 'comment_untitled_title', 'bw'];

		$HTML->setPrintMode(false);
		echo $HTML->tag('div', [
			'class'	=> $HTML->class([
				'comment_editable_content',
				($Actor->hasPermission('editcomments', $this->Author->getRandId())) ? 'user-can-edit' : ''
			])
		],
			$HTML->tag('div', [
				'class'	=> $HTML->class($classes)
			], $Title).
			$HTML->tag('div', [
				'class' => $HTML->class([
					'comment_backup',
					'comment_text_backup',
					'hidden'
				])
				], $Content
			).
			$HTML->tag('div', [
				'class'	=> $HTML->class([
					'comment_text',
					'bw',
					(!$Title) ? 'no-title' : ''
				])
				], $Content
			)
		);
		$HTML->setPrintMode(true);
		/* text */

		$HTML->close("div_comment_content_$idRand");
		/* content */

		/* BOTTOMLINKS */
		if (p('editcomments', $Author) ||
			p('writereplies', $Author) ||
			p('hidecomments', $Author) ||
			p('deletecomments', $Author)
		) {
			$HTML->open("div_comment_action_rail_$idRand", 'div', [
				'class'	=> $HTML->class(['comment_tool_rail', 'comment_action_rail'])
			]);
			$HTML->open("div_comment_action_rail_wrapper_$idRand", 'div', [
				'class'	=> $HTML->class(['rail_wrapper'])
			]);
			$HTML->open("div_comment_action_rail_main_$idRand", 'div', [
				'class'	=> $HTML->class(['rail_main'])
			]);

			/* BOTTOMLINKS--REPLYLINK */
			if (p('writereplies', $Author)) {
				$HTML->setPrintMode(false);
				echo $HTML->divClass(['action_button', 'action_reply'],
					$HTML->span(msg('comm-reply', 1), ['no-selection'])
				);
				$HTML->setPrintMode(true);
			}
			/* bottomlinks--replylink */

			/* BOTTOMLINKS--AJAXRESPONSE */
			if (p('writereplies', $Author)) {
				$HTML->setPrintMode(false);
				echo $HTML->divClass(['ajax_response_container'],
					$HTML->span('Test', ['ajax_response_text'])
				);
				$HTML->setPrintMode(true);
			}
			/* bottomlinks--ajaxresponse */

			/* BOTTOMLINKS--RIGHT */
			$HTML->open("div_comment_action_rail_right_$idRand", 'div', [
				'class'	=> $HTML->class(['rail_right', 'action_button_group'])
			]);

			/* BOTTOMLINKS--DELETELINK */
			if (p('deletecomments', $Author)) {
				$HTML->setPrintMode(false);
				echo $HTML->divClass(['action_button', 'action_delete'],
					$HTML->span(
						msg('comm-delete', 1),
						['activateCommentEditor', 'editlink', 'no-selection']
					)
				);
				$HTML->setPrintMode(true);
			}
			/* bottomlinks--deletelink */

			/* BOTTOMLINKS--HIDELINK */
			if (p('hidecomments', $Author)) {
				$HTML->setPrintMode(false);
				echo $HTML->divClass(['action_button', 'action_hide', $this->Data['hidden'] ? 'action_unhide' : ''],
				$HTML->span(
					msg($this->Data['hidden'] ? 'comm-unhide' : 'comm-hide', 1),
					['activateCommentEditor', 'editlink', 'no-selection'],
				)
			);
				$HTML->setPrintMode(true);
			}
			/* bottomlinks--hidelink */

			/* BOTTOMLINKS--EDITLINK */
			if (p('editcomments', $Author)) {
				$HTML->setPrintMode(false);
				echo $HTML->divClass(['action_button', 'action_edit'],
					$HTML->span(msg('comm-edit', 1), [
						'switch-text',
						'switch-text-initial',
						'switch-text-shown',
						'no-selection'
					]).
					$HTML->span(msg('comm-edit-quit', 1), [
						'switch-text',
						'no-selection'
					])
				);
				$HTML->setPrintMode(true);
			}
			/* bottomlinks--editlink */

			$HTML->close("div_comment_action_rail_right_$idRand");
			/* bottomlinks--right */

			$HTML->close("div_comment_action_rail_main_$idRand");

			/* BOTTOMLINKS--BOTTOMRAIL */
			$HTML->open("div_comment_action_rail_second_$idRand", 'div', [
				'class'	=> $HTML->class(['rail_second'])
			]);

			/* BOTTOMLINKS--BOTTOMRAIL--RIGHT */
			$HTML->open("div_comment_action_rail_second_right_$idRand", 'div', [
				'class'	=> $HTML->class(['rail_right', 'action_button_group'])
			]);

			/* BOTTOMLINKS--BOTTOMRAIL--EDITLINK */
			if (p('editcomments', $Author)) {
				$HTML->setPrintMode(false);
				echo $HTML->divClass(['action_button', 'action_quit'],
					$HTML->span(msg('comm-edit', 1), [
						'switch-text',
						'switch-text-initial',
						'switch-text-shown',
						'no-selection'
					]).
					$HTML->span(msg('comm-edit-quit', 1), [
						'switch-text',
						'no-selection'
					])
				);
				$HTML->setPrintMode(true);
			}
			/* bottomlinks--bottomrail--editlink */

			/* BOTTOMLINKS--SUBMIT */
			if (p('editcomments', $Author)) {
				$HTML->setPrintMode(false);
				echo $HTML->divClass(['action_button', 'action_submit'],
					$HTML->span(msg('comm-edit-send', 1), ['no-selection'])
				);
				$HTML->setPrintMode(true);
			}
			/* bottomlinks--submit */

			$HTML->close("div_comment_action_rail_second_right_$idRand");
			/* bottomlinks--bottomrail--right */

			$HTML->close("div_comment_action_rail_second_$idRand", 'div');
			/* bottomlinks--bottomrail */

			$HTML->close("div_comment_action_rail_wrapper_$idRand");
			$HTML->close("div_comment_action_rail_$idRand");
		}
		/* bottomlinks */

		$HTML->close("div_comment_body_$idRand");
		/* body */

		/* REPLYAREA */
		if ($Actor->hasPermission('writereplies', $AuthorID)) {
			$HTML->open("div_replyfield_$idRand", 'div', [
				'class'	=> $HTML->class(['replyfield'])
			]);

			$HTML->close("div_replyfield_$idRand");
		}
		/* replyarea */

		/* COMMENT DATA */
		$HTML->open("div_commentdata_$idRand", 'div', [
			'class' => $HTML->class(['comment_data'])
		]);

		// If has been edited
		if ($EditedBy = $this->getLatestEdit()) {
			$HTML->span(msg('module-comments-latest-edit', 1, [$EditedBy['username'], timestamp($EditedBy['timestamp'], 1)]), ['comment_edited']);
		}

		$HTML->span(timestamp($this->Data['timestamp'], 1), ['comment_timestamp']);

		$HTML->close("div_commentdata_$idRand");
		/* comment data */

		$this->insertReplies();

		$HTML->markLastElem();
		$HTML->close("div_comment_$idRand");

		$HTML->brClear();
		#$HTML->br();
		$HTML->getErrors();
		#$HTML->printStats();
	}
}
