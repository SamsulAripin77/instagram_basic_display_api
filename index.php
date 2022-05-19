<?php
	require_once( 'instagram_basic_display_api.php' );

	$accessToken = 'IGQVJXYjBzSi1KZAUhhUHNyLVc0WmNFcU1IaVEzaTAyUmV1WERPLXhEYUhJVEhJZA1h4M1R3V21uV3czaFJpSUMxaXNrX29lZA1E0R0dJczFWRExJMVZACZAGx0Y2RZAVE9yVkt6LWVRQ3VtMVJNT0NFRzI0WAZDZD';

	$params = array(
		'get_code' => isset( $_GET['code'] ) ? $_GET['code'] : '',
		'access_token' => $accessToken,
		'user_id' => '5272898626109559'
	);
	$ig = new instagram_basic_display_api( $params );
?>
<meta charset="utf-8">
<h1>Instagram Basic Display API</h1>
<hr />
<?php if ( $ig->hasUserAccessToken ) : ?>
	<h4>IG Info</h4>
	<hr />
	<?php $user = $ig->getUser(); ?>
	<pre>
		<?php print_r( $user ); ?>
	</pre>
	<h1>Username: <?php echo $user['username']; ?></h1>
	<h2>IG ID: <?php echo $user['id']; ?></h2>
	<h3>Media Count: <?php echo $user['media_count']; ?></h3>
	<h4>Account Type: <?php echo $user['account_type']; ?></h4>
	<hr />
	
	<?php $usersMedia = $ig->getUsersMedia(); ?>
	<h3>Users Media Page 1 (<?php echo count( $usersMedia['data'] ); ?>)</h3>
	<h4>Raw Data</h4>
	<textarea style="width:100%;height:400px;"><?php print_r( $usersMedia ); ?></textarea>
	<h4>Posts</h4>
	<ul style="list-style: none;margin:0px;padding:0px;">
		<?php foreach ( $usersMedia['data'] as $post ) : ?>
			<li style="margin-bottom:20px;border:3px solid #333">
				<div>
					<?php if ( 'IMAGE' == $post['media_type'] || 'CAROUSEL_ALBUM' == $post['media_type']) : ?>
						<img style="height:320px" src="<?php echo $post['media_url']; ?>" />
					<?php else : ?>
						<video height="240" width="320" controls>
							<source src="<?php echo $post['media_url']; ?>">
						</video>
					<?php endif; ?>
				</div>
				<div>
					<b>Caption: <a href="<?php echo $post['permalink']; ?>" target="_blank" rel="noopener noreferrer">Go Link</a></b>
				</div>
				<div>
					ID: <?php echo $post['id']; ?>
				</div>
				<div>
					Media Type: <?php echo $post['media_type']; ?>
				</div>
				<div>
					Media URL: <?php echo $post['media_url']; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<a href="<?php echo $ig->authorizationUrl; ?>">
		Authorize w/Instagram
	</a>
<?php endif; ?>