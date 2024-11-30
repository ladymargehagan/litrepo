<?php
class APIClient {
    private $logger;
    private $cache;
    private $lastRequestTime = 0;
    private $requestCount = 0;
    
    public function __construct() {
        $this->logger = new LogHandler();
        $this->cache = new Cache();
    }

    public function request($url, $method = 'GET', $data = null, $headers = []) {
        // Rate limiting check
        $this->enforceRateLimit();
        
        // Build request context
        $context = $this->buildContext($method, $data, $headers);
        
        try {
            // Attempt request with retry logic
            return $this->executeWithRetry($url, $context);
        } catch (Exception $e) {
            $this->logger->error("API request failed: " . $e->getMessage());
            return null;
        }
    }

    private function enforceRateLimit() {
        $currentTime = time();
        if ($currentTime - $this->lastRequestTime >= RATE_LIMIT_WINDOW) {
            $this->requestCount = 0;
            $this->lastRequestTime = $currentTime;
        }
        
        if ($this->requestCount >= MAX_REQUESTS_PER_HOUR - RATE_LIMIT_BUFFER) {
            throw new Exception("Rate limit exceeded");
        }
        
        $this->requestCount++;
    }

    private function buildContext($method, $data, $headers) {
        $defaultHeaders = [
            'User-Agent: Lost in Translation/1.0',
            'Accept: application/json',
            'Connection: close'
        ];
        
        $context = [
            'http' => [
                'method' => $method,
                'header' => array_merge($defaultHeaders, $headers),
                'timeout' => API_TIMEOUT,
                'ignore_errors' => true
            ]
        ];

        if ($data) {
            $context['http']['content'] = is_array($data) ? json_encode($data) : $data;
        }

        return stream_context_create($context);
    }

    private function executeWithRetry($url, $context) {
        $attempts = 0;
        $lastError = null;

        while ($attempts < API_MAX_RETRIES) {
            try {
                $response = @file_get_contents($url, false, $context);
                if ($response === false) {
                    throw new Exception("Request failed");
                }

                // Parse response headers
                $responseHeaders = $this->parseHeaders($http_response_header);
                if ($responseHeaders['status_code'] >= 400) {
                    throw new Exception("API error: " . $responseHeaders['status_code']);
                }

                return $response;
            } catch (Exception $e) {
                $lastError = $e;
                $attempts++;
                if ($attempts < API_MAX_RETRIES) {
                    sleep(API_RETRY_DELAY * $attempts);
                }
            }
        }

        throw $lastError;
    }

    private function parseHeaders($headers) {
        $result = ['status_code' => 0];
        foreach ($headers as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches);
                $result['status_code'] = intval($matches[1]);
            }
        }
        return $result;
    }
} 