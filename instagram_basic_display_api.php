<?php
	require_once( 'defines.php' );

	Class instagram_basic_display_api {
		private $_appId = '469098548321237';
		private $_appSecret = 'fb22f9b1b7f95ddde2abb25932b3d7bf';
		private $_redirectUrl = 'https://mangcoding.com';
		private $_getCode = '';
		private $_apiBaseUrl = 'https://api.instagram.com/';
		private $_graphBaseUrl = 'https://graph.instagram.com/';
		private $_userAccessToken = '';
		private $_userAccessTokenExpires = '';
		private $accessToken = 'IGQVJWek1vbUF5WGNaRXdiblFHSkJDWlB5bUdCN3daam1tRmU4cW1LbU16QllUV3NJVnVQWkpXMVBVbmFMZAzd0NnZAyNTRHVHoyTUttaFBSdXNJeEpCNXJOUTE5UVRIaTM0RlczOWxNOHRkbG5MN3FtOQZDZD';
		private $user_id = '5278301235541381';
		function params() {
			return array(
				'access_token' => $this->accessToken,
				'user_id' => $this->user_id
			);
		} 
			

		public $authorizationUrl = '';
		public $hasUserAccessToken = false;
		public $userId = '';


		function __construct() {
			// save instagram code
			// $this->_getCode = $this->params['get_code'];

			// get an access token
			$this->_setUserInstagramAccessToken( $this->params() );

			// get authorization url
			$this->_setAuthorizationUrl();
		}

		public function getUserAccessToken() {
			return $this->_userAccessToken;
		}

		public function getUserAccessTokenExpires() {
			return $this->_userAccessTokenExpires;
		}

		private function _setAuthorizationUrl() {
			$getVars = array( 
				'app_id' => $this->_appId,
				'redirect_uri' => $this->_redirectUrl,
				'scope' => 'user_profile,user_media',
				'response_type' => 'code'
			);

			// create url
			$this->authorizationUrl = $this->_apiBaseUrl . 'oauth/authorize?' . http_build_query( $getVars );
		}

		private function _setUserInstagramAccessToken( $params ) {
			if ( $params['access_token'] ) { // we have an access token
				$this->_userAccessToken = $params['access_token'];
				$this->hasUserAccessToken = true;
				$this->userId = $params['user_id'];
			} elseif ( $params['get_code'] ) { // try and get an access token
				$userAccessTokenResponse = $this->_getUserAccessToken();
				$this->_userAccessToken = $userAccessTokenResponse['access_token'];
				$this->hasUserAccessToken = true;
				$this->userId = $userAccessTokenResponse['user_id'];

				// get long lived access token
				$longLivedAccessTokenResponse = $this->_getLongLivedUserAccessToken();
				$this->_userAccessToken = $longLivedAccessTokenResponse['access_token'];
				$this->_userAccessTokenExpires = $longLivedAccessTokenResponse['expires_in'];
			}
		}

		private function _getUserAccessToken() {
			$params = array(
				'endpoint_url' => $this->_apiBaseUrl . 'oauth/access_token',
				'type' => 'POST',
				'url_params' => array(
					'app_id' => $this->_appId,
					'app_secret' => $this->_appSecret,
					'grant_type' => 'authorization_code',
					'redirect_uri' => $this->_redirectUrl,
					// 'code' => $this->_getCode
				)
			);

			$response = $this->_makeApiCall( $params );
			return $response;
		}

		private function _getLongLivedUserAccessToken() {
			$params = array(
				'endpoint_url' => $this->_graphBaseUrl . 'access_token',
				'type' => 'GET',
				'url_params' => array(
					'client_secret' => $this->_appSecret,
					'grant_type' => 'ig_exchange_token',
				)
			);

			$response = $this->_makeApiCall( $params );
			return $response;
		}

		public function getUser() {
			$params = array(
				'endpoint_url' => $this->_graphBaseUrl . 'me',
				'type' => 'GET',
				'url_params' => array(
					'fields' => 'id,username,media_count,account_type',
				)
			);

			$response = $this->_makeApiCall( $params );
			return $response;
		}

		public function getUsersMedia() {
			$params = array(
				'endpoint_url' => $this->_graphBaseUrl . $this->userId . '/media',
				'type' => 'GET',
				'url_params' => array(
					'fields' => 'id,caption,media_type,media_url,permalink',
				)
			);

			$response = $this->_makeApiCall( $params );
			return $response;
		}

		public function getPaging( $pagingEndpoint ) {
			$params = array(
				'endpoint_url' => $pagingEndpoint,
				'type' => 'GET',
				'url_params' => array(
					'paging' => true
				)
			);

			$response = $this->_makeApiCall( $params );
			return $response;
		}

		public function getMedia( $mediaId ) {
			$params = array(
				'endpoint_url' => $this->_graphBaseUrl . $mediaId,
				'type' => 'GET',
				'url_params' => array(
					'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username'
				)
			);

			$response = $this->_makeApiCall( $params );
			return $response;
		}

		public function getMediaChildren( $mediaId ) {
			$params = array(
				'endpoint_url' => $this->_graphBaseUrl . $mediaId . '/children',
				'type' => 'GET',
				'url_params' => array(
					'fields' => 'id,media_type,media_url,permalink,url,thumbnail_url,timestamp,username'
				)
			);

			$response = $this->_makeApiCall( $params );
			return $response;
		}

		private function _makeApiCall( $params ) {
			$ch = curl_init();

			$endpoint = $params['endpoint_url'];

			if ( 'POST' == $params['type'] ) { // post request
				curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params['url_params'] ) );
				curl_setopt( $ch, CURLOPT_POST, 1 );
			} elseif ( 'GET' == $params['type']) { // get request
				$params['url_params']['access_token'] = $this->_userAccessToken;

				//add params to endpoint
				$endpoint .= '?' . http_build_query( $params['url_params'] );
			}

			// general curl options
			curl_setopt( $ch, CURLOPT_URL, $endpoint );

			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			$response = curl_exec( $ch );

			curl_close( $ch );

			$responseArray = json_decode( $response, true );

			if ( isset( $responseArray['error_type'] ) ) {
				var_dump( $responseArray );
				die();
			} else {
				return $responseArray;
			}
		}
	}
