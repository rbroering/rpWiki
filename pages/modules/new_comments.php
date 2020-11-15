<?php

require_once("replies_list.php");

/**
 * Represents a single comment which
 * can be requested by providing its
 * random ID.
 */
class Comment {
	private $id;
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

		$Comment = $dbc->prepare("SELECT * FROM comments WHERE rid = :id LIMIT 1");
		$Comment->execute([
			':id'	=> $id
		]);
		$Comment = $Comment->fetch();

		if ($Comment) {
			$this->id		= $id;
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
	 * Prints the comment
	 *
	 * @return void
	 */
	public function insert() : void {
		if (!$this->exists) return;

		global $Wiki, $Actor;

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
			'data-comment-id'	=> $idRand,
			'data-writer'		=> $Author,
			'data-author-groups'=> $this->Author->listGroupsInString(',', true)
		]);

		/* CONTENT */
		$HTML->open("div_comment_content_$idRand", 'div', [
			'class'	=> $HTML->class(['comment_content'])
		]);

		/* TEXT */
		$HTML->setPrintMode(false);
		echo $HTML->tag('div', [
			'class'	=> $HTML->class([
				'comment_editable_content',
				($Actor->hasPermission('editcomments', $this->Author->getRandId())) ? 'user-can-edit' : ''
			])
		],
			(($Title)
				? $HTML->tag('div', [
					'class'	=> $HTML->class(['comment_title', 'title', 'bw'])
				], $Title)
				: ''
			).
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
				echo $HTML->divClass(['action_button', 'action_hide'],
				$HTML->span(
					msg('comm-hide', 1),
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

		$HTML->divClass(['comment_timestamp'],
			timestamp($this->Data['timestamp'])
		);

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