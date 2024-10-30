<?php

class colorwp_twitter_widget extends WP_Widget
{
	
	static private $defaultTweetsToDisplay = 5;
	
	// Widget initialization
	function colorwp_twitter_widget(){
		// Setup basic widget options
		$widget_ops = array('classname' => 'colorwp_twitter_widget', 'description' => 'Displays a configurable number of tweets from any Twitter username in the sidebar.');
		$this->WP_Widget('ColorWP_Twitter_Widget', 'Twitter Widget (by ColorWP.com)', $widget_ops);
	}
 
	// Widget options in admin backend
	function form($instance){
		// Get plugin options
		$instance = wp_parse_args((array) $instance, array( 'title' => '' ));
		
		$num			=	(!empty($instance['num']) ? $instance['num'] : self::$defaultTweetsToDisplay);
		$widget_html	=	(!empty($instance['widget_html']) ? $instance['widget_html'] : null);
		$transparent_bg	=	(!empty($instance['transparent_bg']) ? true : false);
		$link_color		=	(!empty($instance['link_color']) ? $instance['link_color'] : null);
		
		if($link_color && stripos($link_color, '#')===false){
			$link_color = "#$link_color";
		}
		?>

		<p>Due to some recent changes in the Twiter API, widgets must now
			be created <a href="https://twitter.com/settings/widgets/new" 
						  title="Create a Twitter Widget" target="_blank">
			on the Twitter website</a> first.
		</p>
		
		<p>After you create the widget, you will be provided with a HTML code,
			which you need to copy/paste below.</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('widget_html'); ?>">
				<?php _e('Twitter Widget HTML:' , cwp_twitter_widget_plugin::$textdomain); ?>
				<textarea class="widefat" 
						  rows="5"
						  name="<?php echo $this->get_field_name('widget_html'); ?>"
						  ><?php echo $widget_html ? $widget_html : ''; ?></textarea>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('color_theme'); ?>"><?php _e('Color theme:', cwp_twitter_widget_plugin::$textdomain); ?>
				<select name="<?php echo $this->get_field_name('color_theme'); ?>" class="widefat">
					<option value="light">Light</option>
					<option value="dark">Dark</option>
				</select>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('link_color'); ?>">
				<?php _e('Link color:', cwp_twitter_widget_plugin::$textdomain); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('link_color'); ?>" 
							  name="<?php echo $this->get_field_name('link_color'); ?>" type="text" 
							  value="<?php echo $link_color ?>" />
			</label>
		</p>
		
		<p>
			<input type="checkbox" 
				   id="<?php echo $this->get_field_id('transparent_bg'); ?>"
				   name="<?php echo $this->get_field_name('transparent_bg'); ?>"
				   <?php echo $transparent_bg ? "checked='checked'" : ""; ?>>
			<label for="<?php echo $this->get_field_id('transparent_bg'); ?>">Transparent background</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('num'); ?>">
				<?php _e('Number of tweets to show:' , cwp_twitter_widget_plugin::$textdomain); ?>
				<select name="<?php echo $this->get_field_name('num'); ?>" class="">
					<?php for($val=1;$val<=20;$val++): ?>
						<option value="<?php echo $val; ?>" name="<?php echo $val; ?>" <?php echo ($num==$val)?'selected':''; ?>>
							<?php echo $val; ?>
						</option>
					<?php endfor; ?>
				</select>
			</label>
		</p>
		
		<?php
	}
 
	// Process saved values after widget options update
	function update($new_instance, $old_instance){
		$instance                   = $old_instance;
		$instance['title']          = $new_instance['title'];
		$instance['widget_html']	= $new_instance['widget_html'];
		$instance['num']            = (int) $new_instance['num'];
		$instance['link_color']		= $new_instance['link_color'];
		$instance['color_theme']    = $new_instance['color_theme'];
		$instance['transparent_bg']	= $new_instance['transparent_bg'] ? 1 : 0;
		$instance['link_color']		= str_replace('#', '', strip_tags($new_instance['link_color']));
		
		return $instance;
	}
 
	// Widget display in frontend
	function widget($args, $instance){
		extract($args, EXTR_SKIP);

		$original_widget_html = isset($instance['widget_html']) && $instance['widget_html'] ? $instance['widget_html'] : null;
		
		if(!$original_widget_html) // Widget not configured yet
			return;
		
		$colorTheme = isset($instance['color_theme']) && $instance['color_theme'] ? $instance['color_theme'] : null;
		$transparentBg = isset($instance['transparent_bg']) && $instance['transparent_bg'] ? true : false;
		
		$linkColor = isset($instance['link_color']) && $instance['link_color'] ? $instance['link_color'] : null;
		
		// How much tweets to display [1-20]
		$tweetCount = isset($instance['num']) && $instance['num'] ? (int) $instance['num'] : null;
		
		$widgetData = self::extractWidgetData($original_widget_html);
		$username = isset($widgetData['username']) && $widgetData['username'] ? $widgetData['username'] : null;
		$widgetId = isset($widgetData['widget_id']) && $widgetData['widget_id'] ? $widgetData['widget_id'] : null;
		
		echo (isset($before_widget) && !empty($before_widget) ? $before_widget : '');
		
		echo "<a data-chrome='".($transparentBg ? 'transparent' : '')."' class='twitter-timeline' 
				href='https://twitter.com/$username' data-widget-id='$widgetId'
				".($tweetCount ? " data-tweet-limit='$tweetCount' " : "")
				.($colorTheme ? " data-theme='$colorTheme' " : "")
				.($linkColor ? " data-link-color='#$linkColor' " : "")
				.">
				Tweets by @$username</a>";

		echo (isset($after_widget) && $after_widget ? $after_widget : '');
		
		// Include the code JS that parses the <a> tag and displays the widget
		add_action('wp_footer', array('colorwp_twitter_widget', 'showHelperTwitterJs'));
	}
	
	static private function extractWidgetData($twitterWidgetHtml){
		$matches = null;
		
		$widgetId = null;
		$twitterUser = null;
		
		// Try to find widget ID
		preg_match('#data-widget-id="(?P<widgetid>\d+)*"#i', $twitterWidgetHtml, $matches);
		if(isset($matches['widgetid']) && trim($matches['widgetid'])){
			$widgetId = trim($matches['widgetid']);
		}
		
		// Try to find Twitter username
		preg_match('#//twitter.com/(?P<username>\w+)#i', $twitterWidgetHtml, $matches);
		if(isset($matches['username']) && $matches['username']){
			$twitterUser = trim($matches['username']);
		}
		
		return array(
			'username'=>$twitterUser,
			'widget_id'=>$widgetId,
		);
	}
	
	static public function showHelperTwitterJs(){
		echo "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\"://platform.twitter.com/widgets.js\";fjs.parentNode.insertBefore(js,fjs);}}(document,\"script\",\"twitter-wjs\");</script>";
	}
}