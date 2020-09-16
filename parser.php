<?php

namespace glotstats;

function parse( $locale = false, $directory = false, $view, $stats ) {

	if ( empty( $locale ) || empty( $directory ) ) {
		return false;
	}

	$url = 'https://translate.wordpress.org/locale/' . $locale . '/default/stats/' . $directory;

	$file  = file_get_contents( $url );
	$file  = str_replace( '&', '&amp;', $file );
	$start = strpos( $file, '<tbody' );
	$end   = strpos( $file, '</tbody>' );

	$file  = substr( $file, $start, ( $end - $start ) + 8 );
	$xml   = new \SimpleXMLElement( $file );
	$input = array();

	foreach ( $xml as $item ) {

		$input[] = array(
			'directory'         => $directory,
			'title'             => (string) $item->th->a,
			'installs'          => (int) $item->th['data-sort-value'],
			'link'              => (string) $item->th->a['href'],
			'percent'           => (int) $item->td[0]['data-sort-value'],
			'language_link'     => (string) $item->td[0]->a['href'],
			'translated'        => (int) $item->td[1]['data-sort-value'],
			'translated_link'   => (string) $item->td[1]->a['href'],
			'untranslated'      => (int) $item->td[2]['data-sort-value'],
			'untranslated_link' => (string) $item->td[2]->a['href'],
			'fuzzy'             => (int) $item->td[3]['data-sort-value'],
			'fuzzy_link'        => (string) $item->td[3]->a['href'],
			'waiting'           => (int) $item->td[4]['data-sort-value'],
			'waiting_link'      => (string) $item->td[4]->a['href'],
		);
	}

	if ( 'top' === $view ) {
		render_top( $input, $directory, $stats );
	} elseif ( 'tasks' === $view ) {
		render_tasks( $input, $stats );
	}

}

function render_top( $input, $directory, $stats ) {
	$base_url = 'https://translate.wordpress.org';
	$count    = count( $input );

	if ($directory == 'themes') {
		$directory = __('Theme', 'glotpress-stats');
	} else {
		$directory = __('Plugin', 'glotpress-stats');
	}

	?>
	<div class="stats-table">
		<table id="stats-table">
			<thead>

	<tr>
	<th style="width: 60px;">#</th>
	<?php /* translators: Plugin/Theme */ ?>
	<th><?php echo sprintf(__('%s name', 'glotpress-stats'), $directory); ?></th>
	<th><?php _e('Installs', 'glotpress-stats'); ?></th>
	<th><?php _e('Untranslated', 'glotpress-stats'); ?></th>
	</tr>
	</thead>
	<tbody>

	<?php
	$clean         = true;
	$top           = $count;
	$untranslated  = 0;
	$printed_tasks = 0;

	for ( $i = 0; $i < $count; $i++ ) {
		$row = $input[ $i ];

		if ( 0 === $row['untranslated'] ) {
			if ( $clean ) {				
			}
		} else {

			$untranslated += $row['untranslated'];

			if ( $clean ) {
				$clean = false;
			}
			echo '<tr>' . "\n";
			echo '<th>' . ( $i + 1 ) . '</th>';
			echo '<th>' . $row['title'] . '</td>';
			echo '<td>' . number_format( $row['installs'], 0, '', '.' ) . '</td>';
			echo '<td><a href="' . $base_url . $row['untranslated_link'] . '" rel="nofollow">' . $row['untranslated'] . '</a></td>';
			echo '</tr>' . "\n";
			$printed_tasks++;
		}
	}

	echo '</tbody></table><div>';
		$completed = $top - $printed_tasks;
		$percent_completed = round(($completed * 100) / $top,2);
		$percent_remaining = 100 - $percent_completed;

		if ( $printed_tasks > 0){
		echo '<h2>' . __('Overview', 'glotpress-stats') . '</h2>';
		/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes   */
		echo '<p style="font-size: 1.25em; color: #ff9800">' . sprintf(__('Currently, there are %1$s projects remaining (%2$s strings) to complete Top %3$s', 'glotpress-stats'), $printed_tasks, $untranslated, $top) . '</p>';		
	} else {
		echo '<h2>' . __('Overview', 'glotpress-stats') . '</h2>';		
		echo '<p style="font-size: 1.25em; color: #4caf50">' . __('Yay! All the projects have been completed!', 'glotpress-stats') . '</p>';
	}
		?>
	
	<h2><?php _e('Detailed Stats', 'glotpress-stats') ?></h2>
	<ul>
		<li>🔝 <strong><?php echo __('Projects in the Top:', 'glotpress-stats') . '</strong> '. $top; ?></li>
		<li>✅ <strong><?php echo __('Projects completed:', 'glotpress-stats') . '</strong> ' . $completed . ' (' . $percent_completed . '&nbsp;%)'; ?></li>
		<li>🔄 <strong><?php echo __('Projects remaining:', 'glotpress-stats') . '</strong> ' . $printed_tasks . ' (' . $percent_remaining . '&nbsp;%)'; ?></li>
		<li>✏ <strong><?php echo __('Strings remaining:', 'glotpress-stats') . '</strong> ' . $untranslated; ?></li>
	</ul>
	<?php	
}


function render_tasks( $input, $stats ) {
	$base_url = 'https://translate.wordpress.org';
	$count    = count( $input );
	echo '<pre>';

	$clean         = true;
	$top           = $count;
	$untranslated  = 0;
	$printed_tasks = 0;

	if ($printed_tasks > 0) {
		$detailed_stats = '<h2>' . __('Overview', 'glotpress-stats') . '</h2>';
		/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes   */
		$detailed_stats .=  '<p style="font-size: 1.25em; color: #ff9800">' . sprintf(__('There are %1$s strings pending translation for these %2$s projects.', 'glotpress-stats'), $untranslated, $printed_tasks) . '</p>';
	} else {
		$detailed_stats =  '<h2>' . __('Overview', 'glotpress-stats') . '</h2>';
		$detailed_stats .= '<p style="font-size: 1.25em; color: #4caf50">' . __('Yay! All the projects have been completed!', 'glotpress-stats') . '</p>';
	}

	if ($stats == 'top' || $stats == NULL) {
		echo $detailed_stats;
	}

	for ( $i = 0; $i < $count; $i++ ) {
		$row = $input[ $i ];

		if ( 0 === $row['untranslated'] ) {
			if ( $clean ) {
				$top   = $i + 1;
			}
		} else {
			
			if ( $clean ) {
				$clean = false;	
			}
			if ( $printed_tasks < 3 ) {
				/* translators: 1. Active installations */
				echo '*' . $row['title'] . '* (' . sprintf(__('%s+ active installations)', 'glotpress-stats'), number_format($row['installs'], 0, '', '.') ) . "\n";				
				echo $row['untranslated'] . ' ' . __('untranslated strings', 'glotpress-stats') . "\n";
				echo $base_url . $row['untranslated_link'] . "\n\n";
				$printed_tasks++;
				$untranslated += $row['untranslated'];
			}
		}
	}

	echo '</pre>';

	if ($stats == 'bottom') {
		echo $detailed_stats;
	}
	
}