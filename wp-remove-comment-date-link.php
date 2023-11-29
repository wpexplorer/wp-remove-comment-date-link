<?php
/*
Plugin Name: WP Remove Comment Date Link
Description: This plugin removes the date link from WordPress comments by overriding the default comment output.
Version: 1.0
Author: Aj Clarke
Author URI: https://www.wpexplorer.com/
License: GPLv2
*/

final class WP_Remove_Comment_Date_Link {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'wp_list_comments_args', [ $this, 'filter_wp_list_comments_args' ] );
	}

	/**
	 * Modify the comment list arguments.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_list_comments/
	 */
	public function filter_wp_list_comments_args( $args ): array {
		$args['callback'] = [ $this, 'comment_callback' ];
		return $args;
	}

	/**
	 * Custom comment output.
	 *
	 * @link https://developer.wordpress.org/reference/classes/walker_comment/html5_comment/
	 */
	public function comment_callback( $comment, $args, $depth ): void {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

		$commenter          = wp_get_current_commenter();
		$show_pending_links = ! empty( $commenter['comment_author'] );

		if ( $commenter['comment_author_email'] ) {
			$moderation_note = __( 'Your comment is awaiting moderation.' );
		} else {
			$moderation_note = __( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.' );
		}
		?>
		<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $args['has_children'] ? 'parent' : '', $comment ); ?>>
			<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<?php
						if ( 0 != $args['avatar_size'] ) {
							echo get_avatar( $comment, $args['avatar_size'] );
						}
						?>
						<?php
						$comment_author = get_comment_author_link( $comment );

						if ( '0' == $comment->comment_approved && ! $show_pending_links ) {
							$comment_author = get_comment_author( $comment );
						}

						printf(
							/* translators: %s: Comment author link. */
							__( '%s <span class="says">says:</span>' ),
							sprintf( '<b class="fn">%s</b>', $comment_author )
						);
						?>
					</div><!-- .comment-author -->

					<div class="comment-metadata">
						<?php
						printf(
							'<time datetime="%s">%s</time>',
							get_comment_time( 'c' ),
							sprintf(
								/* translators: 1: Comment date, 2: Comment time. */
								__( '%1$s at %2$s' ),
								get_comment_date( '', $comment ),
								get_comment_time()
							)
						);

						edit_comment_link( __( 'Edit' ), ' <span class="edit-link">', '</span>' );
						?>
					</div><!-- .comment-metadata -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
					<em class="comment-awaiting-moderation"><?php echo $moderation_note; ?></em>
					<?php endif; ?>
				</footer><!-- .comment-meta -->

				<div class="comment-content">
					<?php comment_text(); ?>
				</div><!-- .comment-content -->

				<?php
				if ( '1' == $comment->comment_approved || $show_pending_links ) {
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => 'div-comment',
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
								'before'    => '<div class="reply">',
								'after'     => '</div>',
							)
						)
					);
				}
				?>
			</article><!-- .comment-body -->
		<?php
	}

}

if ( ! is_admin() ) {
	new WPEX_Remove_Comment_Date_Link;
}
