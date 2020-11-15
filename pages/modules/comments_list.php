<?php

require_once("new_comments.php");

/**
 * A collection of all comments which are related
 * to the page whose random ID is provided in the
 * constructor.
 */
class CommentsList {
    private $Page;
    private $Comments = null;

    /**
     * Begins collecting all comments related to
     * the page whose random ID is provided by
     * $pageId.
     *
     * @param String $pageId
     */
    public function __construct(String $pageId) {
        global $GlobalImport;
        extract($GlobalImport);

        $Comments = $dbc->prepare("SELECT rid FROM comments WHERE page = :page ORDER BY timestamp DESC LIMIT 10");
        $Comments->execute([
            ':page' => $pageId
        ]);
        $Comments = $Comments->fetchAll();

        $this->Page = $pageId;
        $this->Comments = $Comments;
    }


    /**
     * Inserts the loaded comments
     *
     * @return void
     */
    public function insert() : void {
        global $GlobalImport;
        extract($GlobalImport);

        $HTML->setPrintMode(true);
		$HTML->setAutoIndent(true);
		$HTML->setAutoNl(true);
		$HTML->open('div_eCommentsTimeline', 'div', [
            'id'	=> 'e__Comments_Timeline',
            'class' => $HTML->class(['e__Comments_Timeline'])
        ]);

        foreach ($this->Comments as $CommentId) {
            $Comment = new Comment($this->Page, $CommentId['rid']);
            $Comment->insert();
        }

        $HTML->markLastElem();
		$HTML->close('div_eCommentsTimeline');
    }
}
