<?php

if ( isset($_REQUEST['action']) && 'addnode' == $_REQUEST['action'] ) {
	check_admin_referer( 'add-node', '_wpnonce_add-node' );

	$data_array = array (
		"name" => $_REQUEST['name'],
		"serverId" => $_REQUEST['serverId'],
		"protocol" => "shadowsocks",
		"password" => $_REQUEST['password'],
		"port" => (int) $_REQUEST['port'],
		"lowerBound" => (int) $_REQUEST['lowerBound'],
		"upperBound" => (int) $_REQUEST['upperBound'],
		"comment" => $_REQUEST['comment'],
	);

	$return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/node", json_encode($data_array));

	$error = $return['error'];
	$http_code = $return['http_code'];
	$response = $return['body'];

	if ($http_code === 201) {
		$redirect = add_query_arg( array(
			'update' => 'add',
		), admin_url('admin.php?page=shadowsocks_hub_nodes') );

		wp_redirect( $redirect );
		die();

	} elseif ($http_code === 400) {
		$error_msg = "Invalid input";
	} elseif ($http_code === 404) {
		$error_msg = "Server does not exist";
	} elseif ($http_code === 409) {
		$error_msg = "Node already exists";
	} elseif ($http_code === 500) {
		$error_msg = "Backend system error (addNode)";
	} elseif ($error) {
		$error_msg = "Backend system error: ".$error;
	} else {
		$error_message = "Backend system error undetected error.";
	}
	
	$redirect = add_query_arg( array(
		'error' => urlencode($error_msg),
	), admin_url('admin.php?page=shadowsocks_hub_add_node') );

	wp_redirect( $redirect );
	die();
}
?>
<div class="wrap">
<h1 id="add-new-node"><?php _e( 'Add New Node' ); ?>
</h1>

<?php if ( isset($_REQUEST['error']) ) : ?>
	<div class="error">
		<ul>
		<?php
			$err = urldecode($_REQUEST['error']);
			echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif;

if ( ! empty( $messages ) ) {
	foreach ( $messages as $msg )
		echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
} ?>
<?php
	$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/server/all", false);

    $error = $return['error'];
    $http_code = $return['http_code'];
	$response = $return['body'];

	$data = array();
	if ($http_code === 200) {
		$allServers = $response;
	} elseif ($http_code === 500) {
		$error_message = "Backend system error (getAllServers)";
	} elseif ($error) {
		$error_message = "Backend system error: ".$error;
	} else {
		$error_message = "Backend system error undetected error.";
	}; 

	if ($http_code !== 200) { ?>
		<div class="error">
		<ul>
		<?php
			echo "<li>$error_message</li>\n";
		?>
		</ul>
	</div>
	<?php
	}
	?>


<form method="post" name="addnode" id="addnode" class="validate" novalidate="novalidate">
<input name="action" type="hidden" value="addnode" />
<?php wp_nonce_field( 'add-node', '_wpnonce_add-node' ) ?>

<table class="form-table">
	<tr class="node-name-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Name'); ?></label></th>
		<td><input name="name" type="text" id="name" value="" class="regular-text"/></td>
	</tr>
	<tr class="node-serverId-wrap">
		<th scope="row"><label for="role"><?php _e('Server'); ?></label></th>
		<td><select name="serverId" id="server">
			<?php
			foreach($allServers as $server) {
				echo '<option value ="'.$server["id"].'">'.$server["ipAddressOrDomainName"].'</option>';
			}
			?>
			</select>
		</td>
	</tr>
	<tr class="node-password-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Password'); ?></label></th>
		<?php $initial_password = wp_generate_password( 15 ); ?>
		<td><input type="text" name="password" id="password" value="<?php echo esc_attr( $initial_password ); ?>" class="regular-text"/></td>
	</tr>
	<tr class="node-port-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Port'); ?></label></th>
		<td><input name="port" type="number" id="port" value="" min="1" max="65535" class="regular-text"/></td>
	</tr>
	<tr class="node-lowerBound-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Lower Bound'); ?></label></th>
		<td><input name="lowerBound" type="number" id="lowerBound" value="" min="1" max="65535" class="regular-text"/></td>
	</tr>
	<tr class="node-upperBound-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Upper Bound'); ?></label></th>
		<td><input name="upperBound" type="number" id="upperBound" value="" min="1" max="65535" class="regular-text"/></td>
	</tr>
	<tr class="node-comment-wrap">
		<th scope="row">
			<label for="addnode-host">
				<?php echo __('Comment'); ?>
				<span class="description"><?php _e( '(optional)' ); ?></span>
			</label></th>
		<td><input name="comment" type="text" id="comment" value="" class="regular-text"/></td>
	</tr>
</table>

<?php submit_button( __( 'Add Node' )); ?>
</form>