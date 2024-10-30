<?php
/*
 * Indexy - a WordPress plugin to manage glossary pages.
 *
 * Visit the plugin's webpage at http://www.damnleet.com/indexy
 *
 * Copyright 2015 Jeroen Treurniet (contact@damnleet.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*
 * This is a sample of a single-glossary.php file, that you can place in your
 * theme's folder. If present, WordPress will use this file to present single
 * glossary pages.
 *
 * This file is meant to be used with WordPress' default TwentyFifteen theme. To
 * use it, simply copy it into the wp-content/themes/twentyfifteen folder. The
 * main difference between this file and how TwentyFifteen would normally handle
 * a glossary page is that a list of synonyms for the term (if any exist, that
 * is) is displayed under the page's title, and the post thumbnail, author bio,
 * and previous/next post sections are removed.
 *
 * You can further enhance the page by using the 'Indexy: Related Posts' widget.
 * This widget only appears on single glossary pages, and displays a list of
 * posts that are related to that term.
 */

get_header();
the_post();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header>

			<div class="entry-content">
				<?php

					$synonyms = Indexy::get_synonyms();
					if( sizeof($synonyms) > 0 )
					{
						echo "<p class=\"glossary-synonyms\">\n";
						echo "<i>also:</i> " . implode( ', ', $synonyms );
						echo "</p>";
					}

					/* translators: %s: Name of current post */
					the_content( sprintf(
						__( 'Continue reading %s', 'twentyfifteen' ),
						the_title( '<span class="screen-reader-text">', '</span>', false )
					) );

					wp_link_pages( array(
						'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfifteen' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
						'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>%',
						'separator'   => '<span class="screen-reader-text">, </span>',
					) );
				?>
			</div>

			<footer class="entry-footer">
				<?php twentyfifteen_entry_meta(); ?>
				<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
			</footer>

		</article>

		<?php
		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;
		?>

		</main>
	</div>

<?php get_footer(); ?>