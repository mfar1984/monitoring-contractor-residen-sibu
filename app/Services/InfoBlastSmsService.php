<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class InfoBlastSmsService
{
    protected $apiUrl;
    protected $username;
    protected $password;
    protected $senderId;

    public function __construct()
    {
        $this->apiUrl = \App\Models\IntegrationSetting::getSetting('sms', 'api_url') ?? 'http://www.infoblast.com.my/openapi/';
        $this->username = \App\Models\IntegrationSetting::getSetting('sms', 'username');
        $this->password = \App\Models\IntegrationSetting::getSetting('sms', 'password');
        $this->senderId = \App\Models\IntegrationSetting::getSetting('sms', 'sender_id');
    }

    /**
     * Login to InfoBlast OpenAPI and get session token
     * 
     * @return array Response with success status and token
     */
    protected function login()
    {
        try {
            // Check if we have cached token
            $cachedToken = Cache::get('infoblast_token_' . md5($this->username));
            if ($cachedToken) {
                return [
                    'success' => true,
                    'token' => $cachedToken
                ];
            }

            $loginUrl = rtrim($this->apiUrl, '/') . '/login.php';

            Log::info('InfoBlast Login Request', [
                'url' => $loginUrl,
                'username' => $this->username
            ]);

            // Login to get token
            $response = Http::asForm()->post($loginUrl, [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            $responseBody = $response->body();

            Log::info('InfoBlast Login Response', [
                'status' => $response->status(),
                'body' => $responseBody
            ]);

            if ($response->successful()) {
                // Parse XML response - InfoBlast returns XML format
                $xml = @simplexml_load_string($responseBody);
                
                if ($xml !== false && isset($xml['status']) && (string)$xml['status'] === 'ok') {
                    if (isset($xml->sessionid)) {
                        $token = (string)$xml->sessionid;
                        
                        // Cache token for 30 minutes
                        Cache::put('infoblast_token_' . md5($this->username), $token, 1800);
                        
                        return [
                            'success' => true,
                            'token' => $token
                        ];
                    }
                } elseif ($xml !== false && isset($xml['status']) && (string)$xml['status'] === 'fail') {
                    $errorMsg = isset($xml->error) ? (string)$xml->error : 'Login failed';
                    return [
                        'success' => false,
                        'message' => 'Login failed: ' . $errorMsg
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Login failed: ' . $responseBody
            ];

        } catch (\Exception $e) {
            Log::error('InfoBlast Login Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Login exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via InfoBlast OpenAPI
     * 
     * @param string $phoneNumber Phone number with country code (e.g., +60123456789)
     * @param string $message SMS message content
     * @return array Response with success status and message
     */
    public function sendSms($phoneNumber, $message)
    {
        try {
            // Validate configuration
            if (!$this->username || !$this->password || !$this->senderId) {
                return [
                    'success' => false,
                    'message' => 'SMS configuration is incomplete. Please configure InfoBlast settings first.'
                ];
            }

            // Login first to get token
            $loginResult = $this->login();
            if (!$loginResult['success']) {
                return $loginResult;
            }

            $token = $loginResult['token'];

            // Clean phone number (remove spaces, dashes, etc.)
            $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
            
            // Remove + from phone number if present
            $phoneNumber = str_replace('+', '', $phoneNumber);

            // InfoBlast OpenAPI send SMS endpoint
            $sendUrl = rtrim($this->apiUrl, '/') . '/sendmsg.php';

            // Prepare request parameters
            $params = [
                'sessionid' => $token,
                'msgtype' => 'text',
                'message' => $message,
                'to' => $phoneNumber,
            ];

            Log::info('InfoBlast SMS Request', [
                'url' => $sendUrl,
                'sender' => $this->senderId,
                'phone' => $phoneNumber,
                'message_length' => strlen($message)
            ]);

            // Send SMS request
            $response = Http::asForm()->post($sendUrl, $params);

            $responseBody = $response->body();

            Log::info('InfoBlast SMS Response', [
                'status' => $response->status(),
                'body' => $responseBody
            ]);

            // Check response
            if ($response->successful()) {
                // Parse XML response - InfoBlast returns XML format
                // Expected: <messageid>021275884549026</messageid>
                $xml = @simplexml_load_string($responseBody);
                
                if ($xml !== false && isset($xml->messageid)) {
                    $messageId = (string)$xml->messageid;
                    return [
                        'success' => true,
                        'message' => 'SMS sent successfully to ' . $phoneNumber,
                        'message_id' => $messageId,
                        'response' => $responseBody
                    ];
                }
                
                // Check if response contains messageid tag
                if (stripos($responseBody, '<messageid>') !== false) {
                    return [
                        'success' => true,
                        'message' => 'SMS sent successfully to ' . $phoneNumber,
                        'response' => $responseBody
                    ];
                }
                
                // If no messageid, it might be an error
                return [
                    'success' => false,
                    'message' => 'Unknown response: ' . substr($responseBody, 0, 200)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'HTTP Error ' . $response->status() . ': ' . substr($responseBody, 0, 200)
                ];
            }

        } catch (\Exception $e) {
            Log::error('InfoBlast SMS Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send test SMS
     * 
     * @param string $phoneNumber Phone number to send test SMS
     * @return array Response with success status and message
     */
    public function sendTestSms($phoneNumber)
    {
        $message = 'Test SMS from Monitoring System. InfoBlast integration is working correctly.';
        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Check if SMS service is configured
     * 
     * @return bool
     */
    public function isConfigured()
    {
        return !empty($this->username) && !empty($this->password) && !empty($this->senderId);
    }

    /**
     * Get current configuration status
     * 
     * @return array
     */
    public function getConfigStatus()
    {
        return [
            'configured' => $this->isConfigured(),
            'api_url' => $this->apiUrl,
            'username' => $this->username ? '***' : null,
            'password' => $this->password ? '***' : null,
            'sender_id' => $this->senderId,
        ];
    }
}
