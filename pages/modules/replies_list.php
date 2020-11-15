<?php

require_once("replies.php");

/**
 * A collection of all comments which are related
 * to the page whose random ID is provided in the
 * constructor.
 */
class RepliesList {
    private $toCommentId;
    private $Replies = null;

    /**
     * Begins collecting all comments related to
     * the page whose random ID is provided by
     * $pageId.
     *
     * @param String $pageId
     * @param String $toCommentId
     */
    public function __construct(String $pageId, String $toCommentId) {
        global $GlobalImport;
        extract($GlobalImport);

        $Replies = $dbc->prepare("SELECT rid FROM comments WHERE page = :page AND toRid = :toId ORDER BY timestamp ASC LIMIT 10");
        $Replies->execute([
            ':page' => $pageId,
            ':toId' => $toCommentId
        ]);
        $Replies = $Replies->fetchAll();

        $this->toCommentId  = $toCommentId;
        $this->Replies      = $Replies;
    }


    /**
     * Returns the number of replies to a comment
     *
     * @return integer
     */
    public function countReplies() : int {
        return count($this->Replies);
    }


    /**
     * Inserts the loaded replies
     *
     * @return void
     */
    public function insert() : void {
        global $GlobalImport;
        extract($GlobalImport);

        $HTML->setPrintMode(true);
		$HTML->setAutoIndent(true);
		$HTML->setAutoNl(true);
		$HTML->open('div_eRepliesTimeline', 'div', [
            'id'	=> 'e__Replies_Timeline_' . $this->toCommentId,
            'class' => $HTML->class(['e__Replies_Timeline'])
        ]);

        foreach ($this->Replies as $ReplyId) {
            $Reply = new Reply($this->toCommentId, $ReplyId['rid']);
            $Reply->insert();
        }

        $HTML->markLastElem();
		$HTML->close('div_eRepliesTimeline');
    }
}
