<?php

class Page extends PageBase {

	public function msg($str) {
		switch ($str) {
			case 'pagetitle':
				return msg( 'pt-search', 1 );
			break;
			default:
				return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if (isset( $_GET['q'] )) {
			$query = $_GET['q'];
			if ($query === '*') { $query = ''; }

			$find_in = '';
			if (!empty( $_POST['find_in'] )) {
				$find_in = $_POST['find_in'];
			} else {
				$find_in = 'LOWER(url) OR LOWER(pagetitle) OR LOWER(disptitle) OR LOWER(content)';
			}

			$pagesList = $dbc->prepare( 'SELECT * FROM pages WHERE LOWER(url) OR LOWER(pagetitle) OR LOWER(disptitle) OR LOWER(content) LIKE :searchTerm ORDER BY url' );
			$pagesList->execute([':searchTerm' => '%' . strtolower( $query ) . '%']);
			$resultsNum = $pagesList->rowCount();
			$pagesList = $pagesList->fetchAll();
		} else {
			$resultsNum	= 0;
			$query		= '';
		}
?>
<style type="text/css" >
	.nr {
		min-width: 20px;
		margin-top: -3px;
		padding: 2px 6px;
		background: #FF8969;
		border-bottom-left-radius: 4px;
		border-bottom-right-radius: 4px;
		font-size: 12px;
		text-align: center;
		color: #FFFFFF;
		float: right;
	}
	.title {
		font-size: 18px;
		border-bottom: 1px solid #D3D3D3;
	}
	.preview {
		margin: 10px 0;
		padding: 5px 10px;
		background: #FCF4F4;
		border-radius: 8px;
	}
</style>
<div class="pageHeader" >
	<div class="pageForm" >
		<form method="get" >
			<input type="hidden" name="ref" value="search" /><!-- -->
			<input type="text" name="q" id="pagesearch" class="search searchInput keyword query fi" maxlength="180" placeholder="<?php msg( 'search-ph-keywords' ); ?>" autocomplete="off" /><br />
			<input class="big-submit top10" value="<?php msg( 'search-submit' ); ?>" type="submit" >
		</form>
		<span class="results-text" >
		<?php
			msg( 'search-result-nr', 0, [$resultsNum, $query] );
		?>
		</span>
	</div>
</div>
<div class="pageList" >
<?php
	if (!empty( $pagesList )) {
		#sort($pagesList);
		$CountWithoutHidden = 0;

		foreach ($pagesList as $i => $pages) {
			// DOES USER HAVE PERMISSION TO VIEW PAGE?
			$ShowPage = true;
			if (!empty( $pages['hidden'] )) {
				$AllowedGroups = explode( ',', $pages['hidden'] );
				if (!empty( $AllowedGroups )) {
					$ShowPage = false;

					foreach ($AllowedGroups as $Group) {
						if (ur($Group)) {
							$ShowPage = true;
							break;
						}
					}
				}
			}

			if (!p('search-show-hidden') && !$ShowPage)
				continue;

			// Result
			$Title = (empty( $pages['disptitle'] )) ? $pages['url'] : $pages['disptitle'];
			?>
			<a name="result-<?php echo $CountWithoutHidden + 1; ?>" ></a>
			<div class="result" >
				<span class="nr" >
					<a style="color: #FFFFFF;" href="<?php echo $_SERVER['REQUEST_URI']; ?>#result-<?php echo $CountWithoutHidden + 1; ?>" >
						<?php echo $CountWithoutHidden + 1; ?>
					</a>
				</span>
				<span class="title" ><a href="<?php echo fl('page', ['?' => $pages['url']]); ?>" >
					<?php echo $Title; ?>
				</a></span>
				<div class="preview" >
					<?php
						if (substr($pages['url'], 0, 4) === 'Sys:')
							echo '<code>';
						echo strip_tags(shortStr($pages['content'], 200));
						if (substr($pages['url'], 0, 4) === 'Sys:')
							echo '</code>';
					?>
				</div>
			</div>
			<?php

			$CountWithoutHidden++;
		}

		if ($i + 1 - $CountWithoutHidden > 0)
			echo '<div class="result top30" >' . msg('search-results-hidden-num', 1, $i + 1 - $CountWithoutHidden) . '</div>';
	}
?>
</div>
<?php
	}
}
?>