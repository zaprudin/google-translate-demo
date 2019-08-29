<h1>Переводы названий растений</h1>

<?php
	$args = array(
		'post_type' => array('post'),
		'posts_per_page' => -1,
	);
	$posts = get_posts($args);
?>
<?php if( count( $posts ) ) : ?>
	<table>
		<tr>
			<th>English</th>
			<th>Spanish</th>
			<th>Edit link</th>
		</tr>
		<?php foreach( $posts as $post ) : ?>
			<?php setup_postdata($post); ?>
			<?php 
				$edit_link = get_edit_post_link();
				$en = get_post_meta( get_the_ID(), 'en_title', true );
				$es = get_the_title();
			?>
			<?php if( $es != $en ) : ?>
				<tr>
					<td><?php echo $en; ?></td>
					<td><?php echo $es; ?></td>
					<td><a target="_blank" href="<?php echo $edit_link; ?>">Edit</a></td>
				</tr>
			<?php endif; ?>
			
		<?php endforeach; ?>
		<?php wp_reset_postdata(); ?>
	</table>
<?php endif; ?>
