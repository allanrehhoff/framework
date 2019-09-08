<?php
	function get_top5_recent_modified_files($dir) {
		$mods = array();

		foreach (glob($dir . '/*') as $f) {
			$mods[filemtime($f)] = $f;
		}

		krsort($mods);
		return array_slice($mods, 0, 5, true);
	}

	function human_readable_filesize($bytes, $decimals = 2){
		$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}

	$top5 = get_top5_recent_modified_files(__DIR__);
?>

<table style="width: 30%;">
	<thead>
		<tr>
			<th>Filnavn</th>
			<th>Modificeret</th>
			<th>Størrelse</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($top5 as $timestamp => $file) {
				$file_uri = str_replace(__DIR__, '', $file);
				?>
				<tr>
					<td><a href="<?php print $file_uri; ?>"><?php print basename($file); ?></a></td>
					<td><?php print date("d/m Y H:i:s", $timestamp); ?></td>
					<td><?php print human_readable_filesize(filesize($file)); ?></td>
				</tr>
				<?php
			}
		?>
	</tbody>
</table>