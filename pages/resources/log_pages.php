<?php

class log_pages extends PageElementBase {
	private $Extension	= [];

	public function __construct( $Data ) {
		global $GlobalImport;
		extract( $GlobalImport );

		if (is_string( $Data ))
			$this->Extension['page'] = $Data;
		elseIf (is_array( $Data ))
			$this->Extension['page'] = $Data;

        $Latest = $dbc->prepare("SELECT id, rid, page, username, old, new, notice, timestamp FROM log WHERE type IN (:type) ORDER BY timestamp DESC LIMIT 100");
        $Latest->execute([
            ':type' => 'editpage'
        ]);
		$Latest = $Latest->fetchAll();

		/*$Latest2 = usort($Latest, function ($a, $b) {
			return $a['page'] <=> $b['page'];
		});

		/*echo '<ul>';
		foreach ($Latest as $val)
			echo '<b>' . $val['page'] . '</b> (' . timestamp( $val['timestamp'], 1 ) . ')<br />';
		echo '</ul>';*/
		?>
		<style type="text/css" >
			ul.list .separator {
				padding: 0 5px;
				letter-spacing: 2px;
			}
			ul.list .time {
				width: 50px;
				display: inline-block;
			}
			ul.list .desc {
				font-style: italic;
			}
		</style>
		<?php

		$timestamp = $Latest[0]['timestamp'];

		echo '<ul class="list" style="padding: 0;" >';
		foreach ($Latest as $i1 => $val1) {
			if (substr($val1['timestamp'], 0, 8) < substr($timestamp, 0, 8))
				echo '</ul><h2 class="sectiontitle" >' . timestamp($val1['timestamp'], 1, 'day-monthname') . '</h2><ul class="list" style="padding: 0;" >';

			$PageData = $dbc->prepare("SELECT pagetitle, disptitle, url FROM pages WHERE rid = :rid LIMIT 1");
			$PageData->execute([
				':rid' => $val1['page']
			]);
			$PageData = $PageData->fetch();

			/*$UserData = $dbc->prepare("SELECT username FROM users WHERE rid = :rid LIMIT 1");
			$UserData->execute([
				':rid' => $val1['user']
			]);
			$UserData = $UserData->fetch();*/

			$Pagetitle = (empty($PageData['pagetitle'])) ? $PageData['url'] : $PageData['pagetitle'];
			$Disptitle = (empty($PageData['disptitle'])) ? $Pagetitle : $PageData['disptitle'];
			$DiffBytes = strlen($val1['new']) - strlen($val1['old']);
			if ($DiffBytes > 0) $DiffColor = 'green'; elseIf ($DiffBytes == 0) $DiffColor = 'darkgrey'; else $DiffColor = 'darkred';
			$DiffBytes = '<span style="color: ' . $DiffColor . ';" >(' .
			((abs($DiffBytes) >= 500) ? '<span style="font-weight: bold;" >' .
			(($DiffBytes > 0) ? '+' . $DiffBytes : $DiffBytes) . '</span>' : (($DiffBytes > 0) ? '+' . $DiffBytes : $DiffBytes)) .
			')</span>';

			echo '<li><span class="time" >' . timestamp($val1['timestamp'], 1, 'hour-minute') .
			'</span> ' . al($Disptitle, 'page', ['?' => $PageData['url']]) .
			'<span class="separator" > .. </span>' . $DiffBytes . '<span class="separator" > .. </span>' .
			al($val1['username'], 'user', ['?' => $val1['username']]) . ' (' . al(msg('up-t-contribs', 1), 'user', ['?' => $val1['username'], 'p' => 'contribs']) .
			((p( 'blockuser' )) ? ' | ' . al(msg('block-user-link', 1), 'rights', ['?' => $val1['username'], 'block']) : '') . ')' .
			((!empty($val1['notice'])) ? '<span class="separator" > .. </span>(<span class="desc" >' . $val1['notice'] . '</span>)' : '') . '</li>';

			$timestamp = $val1['timestamp'];
		}
		echo '</ul>';
	}
}