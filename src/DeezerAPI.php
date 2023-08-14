<?php

declare(strict_types=1);

namespace DeezerAPI;

use DeezerAPI\Request;
use DeezerAPI\DeezerAPIException;

class DeezerAPI {

    protected $accessToken = '';
    protected $lastResponse = [];
    protected $options = [
        'auto_retry' => false,
        'return_assoc' => false,
    ];
    protected $request = null;
    protected $session = null;

    /**
     * Constructor
     * Set options and class instances to use.
     *
     * @param array|object $options Optional. Options to set.
     * @param Session $session Optional. The Session object to use.
     * @param Request $request Optional. The Request object to use.
     */
    public function __construct($options = [], $session = null, $request = null) {
        $this->setOptions($options);
        $this->setSession($session);

        $this->request = $request ?? new Request();
    }

    /**
     * Send a request to the Spotify API, automatically refreshing the access token as needed.
     *
     * @param string $method The HTTP method to use.
     * @param string $uri The URI to request.
     * @param array $parameters Optional. Query string parameters or HTTP body, depending on $method.
     * @param array $accessToken Optional. The access token to use.
     *
     * @throws DeezerAPIException
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by the `return_assoc` option.
     * - array headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    protected function sendRequest($method, $uri, $parameters = [], $headers = []) {
        $this->request->setOptions([
            'return_assoc' => $this->options['return_assoc'],
        ]);

        try {
            return $this->request->api($method, $uri, $parameters, $headers);
        } catch (DeezerAPIException $e) {
            if ($this->options['auto_retry'] && $e->isRateLimited()) {
                return $this->sendRequest($method, $uri, $parameters, $headers);
            }

            throw $e;
        }
    }

    /**
     * Add tracks to a playlist.
     * https://developers.deezer.com/api/playlist/tracks
     *
     * @param string $playlistId ID of the playlist to add tracks to.
     * @param string|array $tracks Track IDs to add.
     *
     * @return bool true on success, false on failure.
     */
    public function addPlaylistTracks($playlistId, $tracks) {
        $uri = '/playlist/' . $playlistId . '/tracks';

        $options = [];
        $options['access_token'] = $this->accessToken;
        $options['songs'] = implode(',', (array) $tracks);

        $this->lastResponse = $this->sendRequest('POST', $uri, $options);

        return $this->lastResponse['body'];
    }

    /**
     * Delete tracks from a playlist
     * https://developers.deezer.com/api/playlist/tracks
     *
     * @param string $playlistId ID of the playlist to delete tracks from.
     * @param string|array $tracks Track IDs to delete.
     *
     * @return bool true on success, false on failure.
     */
    public function deletePlaylistTracks($playlistId, $tracks) {
        $uri = '/playlist/' . $playlistId . '/tracks';

        $options = [];
        $options['access_token'] = $this->accessToken;
        $options['songs'] = implode(',', (array) $tracks);

        $this->lastResponse = $this->sendRequest('DELETE', $uri, $options);

        return $this->lastResponse['body'];
    }

    /**
     * Get the current user’s playlists.
     * https://developers.deezer.com/api/user/playlists
     *
     * @param array|object $options Optional. Options for the playlists.
     * - int limit Optional. Limit the number of playlists.
     * - int offset Optional. Number of playlists to skip.
     *
     * @return array|object The user's playlists. Type is controlled by the `return_assoc` option.
     */
    public function getMyPlaylists($options = []) {
        $uri = '/user/me/playlists';

        $options = (array) $options;
        $options['access_token'] = $this->accessToken;
        $this->lastResponse = $this->sendRequest('GET', $uri, $options);

        return $this->lastResponse['body'];
    }

    /**
     * Get the latest full response from the Deezer API.
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by the `return_assoc` option.
     * - array headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function getLastResponse() {
        return $this->lastResponse;
    }

    /**
     * Get a specific playlist.
     * https://developers.deezer.com/api/playlist
     *
     * @param string $playlistId ID of the playlist.
     * @param array|object $options Optional. Options for the playlist.
     *
     * @return array|object The user's playlist. Type is controlled by the `return_assoc` option.
     */
    public function getPlaylist($playlistId, $options = []) {
        $uri = '/playlist/' . $playlistId;

        $options = (array) $options;
        $options['access_token'] = $this->accessToken;

        $this->lastResponse = $this->sendRequest('GET', $uri, $options);

        return $this->lastResponse['body'];
    }

    /**
     * Get the tracks in a playlist.
     * https://developers.deezer.com/api/playlist/tracks
     *
     * @param string $playlistId ID of the playlist.
     * @param array|object $options Optional. Options for the tracks.
     * - int limit Optional. Limit the number of tracks.
     * - int index Optional. Number of tracks to skip.
     *
     * @return array|object The tracks in the playlist. Type is controlled by the `return_assoc` option.
     */
    public function getPlaylistTracks($playlistId, $options = []) {
        $uri = '/playlist/' . $playlistId . '/tracks';

        $options = (array) $options;
        $options['access_token'] = $this->accessToken;

        $this->lastResponse = $this->sendRequest('GET', $uri, $options);

        return $this->lastResponse['body'];
    }

    /**
     * Get a track.
     * https://developers.deezer.com/api/track
     *
     * @param string $trackId ID of the track.
     *
     * @return array|object The requested track. Type is controlled by the `return_assoc` option.
     */
    public function getTrack($trackId) {
        $uri = '/track/' . $trackId;

        $this->lastResponse = $this->sendRequest('GET', $uri);

        return $this->lastResponse['body'];
    }

    /**
     * Get a user.
     * https://developers.deezer.com/api/user
     *
     * @param string $userId ID of the user.
     *
     * @return array|object The requested user. Type is controlled by the `return_assoc` option.
     */
    public function getUser($userId) {
        $uri = '/user/' . $userId;

        $this->lastResponse = $this->sendRequest('GET', $uri);

        return $this->lastResponse['body'];
    }

    /**
     * Get a user's playlists.
     * https://developer.spotify.com/documentation/web-api/reference/#/operations/get-list-users-playlists
     *
     * @param string $userId ID or URI of the user.
     * @param array|object $options Optional. Options for the tracks.
     * - int limit Optional. Limit the number of tracks.
     * - int offset Optional. Number of tracks to skip.
     *
     * @return array|object The user's playlists. Type is controlled by the `return_assoc` option.
     */
    public function getUserPlaylists($userId, $options = []) {
        $uri = '/user/' . $userId . '/playlists';

        $this->lastResponse = $this->sendRequest('GET', $uri, $options);

        return $this->lastResponse['body'];
    }

    /**
     * Search for an item.
     * https://developers.deezer.com/api/search
     *
     * @param string $query The term to search for.
     * @param string|array $type The type of item to search for.
     * @param array|object $options Optional. Options for the search.
     * - int limit Optional. Limit the number of items.
     * - int index Optional. Number of items to skip.
     *
     * @return array|object The search results. Type is controlled by the `return_assoc` option.
     */
    public function search($query, $type, $options = []) {
        $uri = '/search/' . $type;

        $options = array_merge((array) $options, [
            'q' => $query
        ]);


        $this->lastResponse = $this->sendRequest('GET', $uri, $options);

        return $this->lastResponse['body'];
    }

    /**
     * Set the access token to use.
     *
     * @param string $accessToken The access token.
     *
     * @return self
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set options
     *
     * @param array|object $options Options to set.
     *
     * @return self
     */
    public function setOptions($options) {
        $this->options = array_merge($this->options, (array) $options);

        return $this;
    }

    /**
     * Set the Session object to use.
     *
     * @param Session $session The Session object.
     *
     * @return self
     */
    public function setSession($session) {
        $this->session = $session;

        return $this;
    }
}
