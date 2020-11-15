<?php

/**
 * Represents a single reply to ca comment
 * which can be requested by providing its
 * random ID.
 */
class Reply {
	private $toCommentId, $id;
	private $exists = false;
	private $Data;
	private $Author;

	/**
	 * Loads all data and initializes the object.
	 *
	 * @param String $id			The random ID of the reply
	 * @param String $toCommentId	The random ID of the comment that the reply relates to
	 */
    public function __construct(String $toCommentId, String $id) {
		global $GlobalImport;
		extract($GlobalImport);

		$Reply = $dbc->prepare("SELECT * FROM comments WHERE rid = :id AND toRid = :toId AND type = 'reply' LIMIT 1");
		$Reply->execute([
			':id'	=> $id,
			':toId'	=> $toCommentId
		]);
		$Reply = $Reply->fetch();

		if ($Reply) {
			$this->toCommentId = $toCommentId;
			$this->id		= $id;
			$this->exists	= true;
			$this->Data		= $Reply;

			$this->Author = new User();
			$this->Author->setUserByName($Reply['writer']);
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
	 * Prints the reply
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
		$idHTML		= 'comment_' . $this->toCommentId . '__reply_' . $this->id;
		$idNinc		= $this->Data['id'];
		$Author		= $this->Author->getName();
		$AuthorID	= $this->Author->getRandId();
		$Timestamp	= timestamp($this->Data['timestamp'], 1);

		$Hidden		= $this->Data['hidden'];

		$Title		= (!empty($this->Data['title'])) ? $this->Data['title'] : false;
		$Content	= $this->Data['content'];

		$Usericon	= $this->Author->getIcon([100, 100], 'cssurl');

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

		$HTML->open("div_reply_$idHTML", 'div', [
			'id'	=> $idHTML,
			'class'	=>
				$HTML->class([
					'reply',
					($Hidden) ? 'hidden-reply' : ''
				])
		]);

		$HTML->jumpAnchor("c-{$this->toCommentId}-r-$idRand");

		/* USERICON */
		$HTML->open("div_reply_userinfo_$idHTML", 'div', [
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
		$HTML->close("div_reply_userinfo_$idHTML");
		/* usericon */

		/* BODY */
		$HTML->open("div_reply_body_$idHTML", 'div', [
			'class'				=> $HTML->class(['reply_body']),
			'data-reply-id'		=> $idRand,
			'data-writer'		=> $Author,
			'data-author-groups'=> $this->Author->listGroupsInString(',', true)
		]);

		/* CONTENT */
		$HTML->open("div_reply_content_$idHTML", 'div', [
			'class'	=> $HTML->class(['reply_content'])
		]);

		/* TEXT */
		$HTML->setPrintMode(false);
		echo $HTML->tag('div', [
			'class'	=> $HTML->class([
				'reply_editable_content',
				($Actor->hasPermission('editreplies', $this->Author->getRandId())) ? 'user-can-edit' : ''
			])
		],
			$HTML->tag('div', [
				'class' => $HTML->class([
					'reply_backup',
					'reply_text_backup',
					'hidden'
				])
				], $Content
			).
			$HTML->tag('div', [
				'class'	=> $HTML->class([
					'reply_text',
					'bw'
				])
				], $Content
			)
		);
		$HTML->setPrintMode(true);
		/* text */

		$HTML->close("div_reply_content_$idHTML");
		/* content */

		$HTML->close("div_reply_body_$idHTML");
		/* body */
		
		$HTML->close("div_reply_$idHTML");
	}
}