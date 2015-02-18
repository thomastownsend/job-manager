<?php
class JobmanLatestJobsWidget extends WP_Widget {
    /** constructor */
    function JobmanLatestJobsWidget() {
		$name = __( 'Job Manager: Recent Jobs', 'jobman');
		$options = array( 'description' => __( 'A list of the most recent jobs posted to your site', 'jobman' ) );
		
        parent::WP_Widget( false, $name, $options );	
    }

    function widget( $args, $instance ) {		
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;

		$args = array(
					'post_type' => 'jobman_job',
					'numberposts' => -1,
					'suppress_filters' => false
				);
					
		add_filter( 'posts_where', 'jobman_job_live_where' );
		add_filter( 'posts_join', 'jobman_job_live_join' );
		add_filter( 'posts_distinct', 'jobman_job_live_distinct' );
		
		$jobs = get_posts( $args );
		
		remove_filter( 'posts_where', 'jobman_job_live_where' );
		remove_filter( 'posts_join', 'jobman_job_live_join' );
		remove_filter( 'posts_distinct', 'jobman_job_live_distinct' );

		foreach( $jobs as $id => $job ) {
			// Remove jobs not in selected categories
			if( 'selected' == $instance['jobsfrom'] ) {
				$categories = wp_get_object_terms( $job->ID, 'jobman_category' );
				if( count( $categories ) > 0 ) {
					foreach( $categories as $cat ) {
						if( in_array( $cat->term_id, $instance['selected_cats'] ) )
							// Job is in a selected category. Move to next job.
							continue 2;
					}
					
					// Job wasn't in a selected category. Remove it.
					unset( $jobs[$id] );
				}
				else {
					unset( $jobs[$id] );
				}
			}
		}
		
		if( count( $jobs ) > 0 ) {
			echo '<ul>';
			$jobcount = 0;
			foreach( $jobs as $job ) {
				if( $jobcount >= $instance['jobslimit'] )
					break;

				echo '<li><a href="' . get_page_link( $job->ID ) . '">' . $job->post_title . '</a></li>';
				
				$jobcount++;
			}
			echo '</ul>';
		}
		else {
			echo '<p>' . __( 'There are no jobs to display at this time.', 'jobman' ) . '</p>';
		}

		echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
		$new_instance['jobslimit'] = (int)$new_instance['jobslimit'];

		if( $new_instance['jobslimit'] < 0 )
			$new_instance['jobslimit'] = 0;
		else if( $new_instance['jobslimit'] > 15 )
			$new_instance['jobslimit'] = 15;
		
		$new_instance['selected_cats'] = array();
		
		if( array_key_exists( $this->get_field_id( 'selected_cats' ), $_REQUEST ) && is_array( $_REQUEST[$this->get_field_id( 'selected_cats' )] ) ) {
			foreach( $_REQUEST[$this->get_field_id( 'selected_cats' )] as $catid ) {
				$new_instance['selected_cats'][] = $catid;
			}
		}
		
		return $new_instance;
    }

    function form( $instance ) {
		$title = '';
		if( array_key_exists( 'title', $instance ) )
			$title = esc_attr( $instance['title'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'jobman' ); ?>: 
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
<?php 

		$jobslimit = 5;
		if( array_key_exists( 'jobslimit', $instance ) )
			$jobslimit = esc_attr( $instance['jobslimit'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'jobslimit' ); ?>"><?php _e( 'Number of Jobs to show', 'jobman' ); ?>: 
					<input id="<?php echo $this->get_field_id( 'jobslimit' ); ?>" name="<?php echo $this->get_field_name( 'jobslimit' ); ?>" type="text" size="3" value="<?php echo $jobslimit; ?>" />
				</label>
				<small>(<?php _e( 'at most 15', 'jobman' ) ?>)</small>
			</p>
<?php 

		$jobsfrom = 'all';
		if( array_key_exists( 'jobsfrom', $instance ) )
			$jobsfrom = esc_attr( $instance['jobsfrom'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'jobsfrom' ); ?>"><?php _e( 'Show Jobs From', 'jobman' ); ?>: 
					<select id="<?php echo $this->get_field_id( 'jobsfrom' ); ?>" name="<?php echo $this->get_field_name( 'jobsfrom' ); ?>">
						<option value="all"<?php echo ( 'all' == $jobsfrom )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'All Categories', 'jobman' ) ?></option>
						<option value="selected"<?php echo ( 'selected' == $jobsfrom )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Selected Categories', 'jobman' ) ?></option>
					</select>
				</label>
			</p>
<?php 

		$selected_cats = array();
		if( array_key_exists( 'selected_cats', $instance ) )
			$selected_cats = $instance['selected_cats'];

		$categories = get_terms( 'jobman_category', 'hide_empty=0' );
?>
            <p>
				<label><?php _e( 'Categories', 'jobman' ); ?>: </label><br/>
<?php
		if( count( $categories ) > 0 ) {
			foreach( $categories as $cat ) {
				echo "<input type='checkbox' name='" . $this->get_field_id( 'selected_cats' ) . "[]' value='$cat->term_id'";
				if( in_array( $cat->term_id, $selected_cats ) )
					echo ' checked="checked"';
				echo "> $cat->name<br/>";
			}
		}
		else {
			echo '<p>' . __( 'No categories defined.', 'jobman' ) . '</p>';
		}
?>
			</p>
<?php 
	}

}


class JobmanCategoriesWidget extends WP_Widget {
    /** constructor */
    function JobmanCategoriesWidget() {
		$name = __( 'Job Manager: Categories', 'jobman');
		$options = array( 'description' => __( 'A list or dropdown of Job Manager categories', 'jobman' ) );
		
        parent::WP_Widget( false, $name, $options );	
    }

    function widget( $args, $instance ) {
		global $wp_query;
		
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
			
		$dropdown = 0;
		if( array_key_exists( 'dropdown', $instance ) )
			$dropdown = $instance['dropdown'];
		
		$show_counts = 0;
		if( array_key_exists( 'show_counts', $instance ) )
			$show_counts = $instance['show_counts'];

		$hide_empty = 0;
		if( array_key_exists( 'hide_empty', $instance ) )
			$hide_empty = $instance['hide_empty'];

		$categories = get_terms( 'jobman_category', 'hide_empty=0' );
		if( count( $categories ) > 0 ) {
			if( $dropdown ) {
				echo '<select id="jobman-catlist">';
				echo '<option value="">' . __( 'Select Category', 'jobman' ) . '</option>';
			}
			else {
				echo '<ul>';
			}
			
			$count_args = array( 
							'post_type' => 'jobman_job',
							'numberposts' => -1,
							'suppress_filters' => false
						);

			foreach( $categories as $cat ) {
				$selected = '';
				if( array_key_exists( 'jcat', $wp_query->query_vars ) && $wp_query->query_vars['jcat'] == $cat->slug )
					$selected = ' selected="selected"';
				
				$jobs = array();
				if( $hide_empty || $show_counts ) {
					$count_args['jcat'] = $cat->slug;
					add_filter( 'posts_where', 'jobman_job_live_where' );
					add_filter( 'posts_join', 'jobman_job_live_join' );
					add_filter( 'posts_distinct', 'jobman_job_live_distinct' );
					
					$jobs = get_posts( $count_args );
					
					remove_filter( 'posts_where', 'jobman_job_live_where' );
					remove_filter( 'posts_join', 'jobman_job_live_join' );
					remove_filter( 'posts_distinct', 'jobman_job_live_distinct' );
				}
				
				if( $hide_empty && empty( $jobs ) )
					continue;
				
				$count = '';
				if( $show_counts ) {
					$count = ' (' . count( $jobs ) . ')';
				}
				
				if( $dropdown )
					echo "<option value='$cat->slug'$selected>$cat->name$count</option>";
				else
					echo "<li><a href='" . get_term_link( $cat->slug, 'jobman_category' ) . "'>$cat->name$count</a></li>";
			}

			if( $dropdown ) {
?>
		</select>
		
<script type='text/javascript'> 
/* <![CDATA[ */
	var jobman_dropdown = document.getElementById("jobman-catlist");
	function onJobmanCatChange() {
		if ( jobman_dropdown.options[jobman_dropdown.selectedIndex].value != '' ) {
			location.href = "<?php echo get_option( 'home' ) ?>/?jcat="+jobman_dropdown.options[jobman_dropdown.selectedIndex].value;
		}
	}
	jobman_dropdown.onchange = onJobmanCatChange;
/* ]]> */
</script> 
<?php
			}
			else {
				echo '</ul>';
			}
		}
		else {
			echo '<p>' . __( 'There are no categories to display at this time.', 'jobman' ) . '</p>';
		}
					
		echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
		return $new_instance;
    }

    function form( $instance ) {
		$title = '';
		if( array_key_exists( 'title', $instance ) )
			$title = esc_attr( $instance['title'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'jobman' ); ?>: 
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
<?php
		$dropdown = 0;
		if( array_key_exists( 'dropdown', $instance ) )
			$dropdown = $instance['dropdown'];
		
		$show_counts = 0;
		if( array_key_exists( 'show_counts', $instance ) )
			$show_counts = $instance['show_counts'];

		$hide_empty = 0;
		if( array_key_exists( 'hide_empty', $instance ) )
			$hide_empty = $instance['hide_empty'];
?>
            <p>
				<input id="<?php echo $this->get_field_id( 'dropdown' ); ?>" name="<?php echo $this->get_field_name( 'dropdown' ); ?>" type="checkbox" value="1" <?php echo ( $dropdown )?( 'checked="checked" ' ):( '' )?>/> <?php _e( 'Show as dropdown', 'jobman' ) ?><br/>
				<input id="<?php echo $this->get_field_id( 'show_counts' ); ?>" name="<?php echo $this->get_field_name( 'show_counts' ); ?>" type="checkbox" value="1" <?php echo ( $show_counts )?( 'checked="checked" ' ):( '' )?>/> <?php _e( 'Show job counts', 'jobman' ); ?><br/>
				<input id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" type="checkbox" value="1" <?php echo ( $hide_empty )?( 'checked="checked" ' ):( '' )?>/> <?php _e( 'Hide empty categories', 'jobman' ); ?>
			</p>
<?php 
	}

}


class JobmanHighlightedJobsWidget extends WP_Widget {
    /** constructor */
    function JobmanHighlightedJobsWidget() {
		$name = __( 'Job Manager: Highlighted Jobs', 'jobman');
		$options = array( 'description' => __( 'A list jobs that have been marked as highlighted', 'jobman' ) );
		
        parent::WP_Widget( false, $name, $options );	
    }

    function widget( $args, $instance ) {
		global $wp_query;
		
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
			
		$args = array( 
					'post_type' => 'jobman_job',
					'numberposts' => -1,
					'suppress_filters' => false,
					'meta_key' => 'highlighted',
					'meta_value' => 1
				);
		add_filter( 'posts_where', 'jobman_job_live_where' );
		add_filter( 'posts_join', 'jobman_job_live_join' );
		add_filter( 'posts_distinct', 'jobman_job_live_distinct' );
		
		$jobs = get_posts( $args );
		
		remove_filter( 'posts_where', 'jobman_job_live_where' );
		remove_filter( 'posts_join', 'jobman_job_live_join' );
		remove_filter( 'posts_distinct', 'jobman_job_live_distinct' );

		foreach( $jobs as $id => $job ) {
			// Remove expired jobs
			$displayenddate = get_post_meta( $job->ID, 'displayenddate', true );
			if( '' != $displayenddate && strtotime( $displayenddate ) <= time() ) {
				unset( $jobs[$id] );
				continue;
			}

				// Remove future jobs
			$displaystartdate = $job->post_date;
			if( '' != $displaystartdate && strtotime( $displaystartdate ) > time() ) {
				unset( $jobs[$id] );
				continue;
			}
		}
		
		if( count( $jobs ) > 0 ) {
			echo '<ul>';
			foreach( $jobs as $job ) {
				echo '<li><a href="' . get_page_link( $job->ID ) . '">' . $job->post_title . '</a></li>';
			}
			echo '</ul>';
		}
		else {
			echo '<p>' . __( 'There are no jobs to display at this time.', 'jobman' ) . '</p>';
		}
					
		echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
		return $new_instance;
    }

    function form( $instance ) {
		$title = '';
		if( array_key_exists( 'title', $instance ) )
			$title = esc_attr( $instance['title'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'jobman' ); ?>: 
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
<?php 
	}

}


class JobmanJobsWidget extends WP_Widget {
    /** constructor */
    function JobmanJobsWidget() {
		$name = __( 'Job Manager: Selected Jobs', 'jobman');
		$options = array( 'description' => __( 'A customizable list jobs posted to your site', 'jobman' ) );
		
        parent::WP_Widget( false, $name, $options );	
    }

    function widget( $args, $instance ) {		
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;

		$args = array( 
					'post_type' => 'jobman_job',
					'numberposts' => -1,
					'suppress_filters' => false,
					'post__in' => explode( ',', $instance['jobs'] )
				);
		add_filter( 'posts_where', 'jobman_job_live_where' );
		add_filter( 'posts_join', 'jobman_job_live_join' );
		add_filter( 'posts_distinct', 'jobman_job_live_distinct' );
		
		$jobs = get_posts( $args );
		
		remove_filter( 'posts_where', 'jobman_job_live_where' );
		remove_filter( 'posts_join', 'jobman_job_live_join' );
		remove_filter( 'posts_distinct', 'jobman_job_live_distinct' );

		if( count( $jobs ) > 0 ) {
			echo '<ul>';
			foreach( $jobs as $job ) {
				echo '<li><a href="' . get_page_link( $job->ID ) . '">' . $job->post_title . '</a></li>';
			}
			echo '</ul>';
		}
		else {
			echo '<p>' . __( 'There are no jobs to display at this time.', 'jobman' ) . '</p>';
		}

		echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
		return $new_instance;
    }

    function form( $instance ) {
		$title = '';
		if( array_key_exists( 'title', $instance ) )
			$title = esc_attr( $instance['title'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'jobman' ); ?>: 
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
<?php
			$jobs = esc_attr( $instance['jobs'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'jobs' ); ?>"><?php _e( 'Comma separated list of Job IDs', 'jobman' ); ?>: 
					<input class="widefat" id="<?php echo $this->get_field_id( 'jobs' ); ?>" name="<?php echo $this->get_field_name( 'jobs' ); ?>" type="text" value="<?php echo $jobs; ?>" />
				</label>
			</p>
<?php
	}

}

?>