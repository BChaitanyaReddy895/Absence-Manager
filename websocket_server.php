<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require 'vendor/autoload.php';

class MyWebSocketServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if ($data) {
    echo "[WebSocket] Received: " . json_encode($data) . "\n";
} else {
    echo "[WebSocket] Error: Invalid JSON received!\n";
}

        switch ($data['type']) {
            case 'page_load':
                // Handle page load event
                $response = [
                    'type' => 'notification',
                    'message' => 'Welcome to the ' . $data['page'] . ' page!'
                ];
                $from->send(json_encode($response));
                break;

            case 'login':
                // Handle login request
                $email = $data['email'];
                $password = $data['password'];

                // Forward login request to student-login.php
                $loginResponse = $this->validateLogin($email, $password);

                // Send the response back to the client
                $from->send(json_encode($loginResponse));
                break;

            case 'signup':
                // Handle signup request
                $signupResponse = $this->processSignup($data);

                // Send the response back to the client
                $from->send(json_encode($signupResponse));
                break;

            case 'redirect':
                // Handle redirect request
                $response = [
                    'type' => 'notification',
                    'message' => 'Redirecting to ' . $data['page'] . '...'
                ];
                $from->send(json_encode($response));
                break;

            case 'logout':
                // Handle logout request
                $response = [
                    'type' => 'notification',
                    'message' => 'Logging out...'
                ];
                $from->send(json_encode($response));
                break;

            case 'leave_submission':
                // Handle leave form submission
                $leaveResponse = $this->processLeaveForm($data['data']);

                // Send the response back to the client
                $from->send(json_encode($leaveResponse));
                break;

            case 'selection_submission':
                    // Handle selection submission
                    if (isset($data['data']) && isset($data['data']['selection'])) {
                        $selectionResponse = $this->processSelection($data['data']);
                        $from->send(json_encode($selectionResponse));
                    } else {
                        $response = [
                            'type' => 'selection_response',
                            'success' => false,
                            'message' => 'No selection made.'
                        ];
                        $from->send(json_encode($response));
                    }
                break;
            case 'professor_login':
                    // Handle professor login request
                     $email = $data['email'];
                    $password = $data['password'];
            
                    // Forward login request to professor-login.php
                    $loginResponse = $this->validateProfessorLogin($email, $password);
            
                    // Send the response back to the client
                    $from->send(json_encode($loginResponse));
                break;
            case 'professor_signup':
                    // Handle professor signup request
                    $signupResponse = $this->processProfessorSignup($data['data']);
                    $from->send(json_encode($signupResponse));
                break;
            case 'professor_option_selected':
                    // Handle professor option selection
                    $option = $data['option'];
                    echo "Professor selected option: " . $option . "\n";
        
                    // Send a response back to the client (optional)
                    $from->send(json_encode([
                        'type' => 'professor_option_response',
                        'success' => true,
                        'message' => 'Option selected: ' . $option
                    ]));
                break;
                case 'mentor_login':
                    // Handle mentor login request
                    $email = $data['email'];
                    $password = $data['password'];
        
                    // Debug: Log the data being forwarded
                    echo "Forwarding data to mentor-login.php: " . print_r($data, true) . "\n";
        
                    // Forward login request to mentor-login.php
                    $loginResponse = $this->validateMentorLogin($email, $password);
        
                    // Send the response back to the client
                    $from->send(json_encode($loginResponse));
                    break;
        
            case 'mentor_registration':
                    // Handle mentor registration request
                    $registrationResponse = $this->processMentorRegistration($data['data']);
                    $from->send(json_encode($registrationResponse));
                break;
                case 'fetch_leave_forms':
                    // Handle fetch leave forms request
                    $leaveFormsResponse = $this->fetchLeaveForms();
                    $from->send(json_encode($leaveFormsResponse));
                    break;
        
            
        
        
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function validateLogin($email, $password) {
        // Send a request to student-login.php for validation
        $url = 'http://localhost/safersideproject123/project123/student-login.php';
        $postData = http_build_query([
            'REVA_Email_id' => $email,
            'password' => $password
        ]);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            return [
                'type' => 'login_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }

        return json_decode($result, true);
    }

    private function processSignup($data) {
        // Send a request to student-signup.php for signup
        $url = 'http://localhost/safersideproject123/project123/student-signup.php';
        $postData = http_build_query([
            'full_name' => $data['full_name'],
            'SRN' => $data['SRN'],
            'REVA_Email_id' => $data['REVA_Email_id'],
            'password' => $data['password'],
            'phone_number' => $data['phone_number']
        ]);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            return [
                'type' => 'signup_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }

        return json_decode($result, true);
    }

    private function processLeaveForm($formData) {
        // Send a request to leave-form.php for processing
        $url = 'http://localhost/safersideproject123/project123/leave-form.php';
        $postData = http_build_query($formData);
    
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            return [
                'type' => 'leave_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }
    
        // Decode the response from leave-form.php
        $response = json_decode($result, true);
    
        // Ensure the response includes a success flag and message
        if (isset($response['success']) && $response['success']) {
            return [
                'type' => 'leave_response',
                'success' => true,
                'message' => 'Leave form submitted successfully!'
            ];
        } else {
            return [
                'type' => 'leave_response',
                'success' => false,
                'message' => $response['message'] ?? 'An error occurred while submitting the leave form.'
            ];
        }
    }
    
    private function processSelection($selectionData) {
        // Ensure $selectionData is an array and contains the selection key
        if (!is_array($selectionData) || !isset($selectionData['selection'])) {
            return [
                'type' => 'selection_response',
                'success' => false,
                'message' => 'Invalid selection data.'
            ];
        }
    
        // Extract the selection value
        $selection = $selectionData['selection'];
    
        // Send a request to option_event.php for processing
        $url = 'http://localhost/safersideproject123/project123/option_event.php';
        $postData = http_build_query(['selection' => $selection]);
    
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            return [
                'type' => 'selection_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }
    
        return json_decode($result, true);
    }
    private function validateProfessorLogin($email, $password) {
        // Send a request to professor-login.php for validation
        $url = 'http://localhost/safersideproject123/project123/professor-login.php';
        $postData = http_build_query([
            'Reva_Email' => $email,
            'Password' => $password
        ]);
    
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            return [
                'type' => 'login_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }
    
        return json_decode($result, true);
    }
    private function processProfessorSignup($signupData) {
        // Send a request to process_signup.php for processing
        $url = 'http://localhost/safersideproject123/project123/process_signup.php';
    
        // Convert the data to JSON
        $jsonData = json_encode($signupData);
    
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $jsonData
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            return [
                'type' => 'signup_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }
    
        return json_decode($result, true);
    }
    private function validateMentorLogin($email, $password) {
        // Send a request to mentor-login.php for validation
        $url = 'http://localhost/safersideproject123/project123/mentor-login.php';
    
        // Prepare the JSON payload
        $jsonData = json_encode([
            'email' => $email,
            'password' => $password
        ]);
    
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $jsonData
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            return [
                'type' => 'login_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }
    
        return json_decode($result, true);
    }
    private function processMentorRegistration($registrationData) {
        // Send a request to mentor_registration.php for processing
        $url = 'http://localhost/safersideproject123/project123/mentor_registration.php';
        $postData = http_build_query($registrationData);
    
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            return [
                'type' => 'registration_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }
    
        return json_decode($result, true);
    }
    private function fetchLeaveForms() {
        // Send a request to mentor-dashboard.php for fetching leave forms
        $url = 'http://localhost/safersideproject123/project123/mentor-dashboard.php';
    
        $options = [
            'http' => [
                'method' => 'GET'
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            return [
                'type' => 'leave_forms_response',
                'success' => false,
                'message' => 'Unable to connect to the login server. Error: ' . error_get_last()['message']
            ];
        }
    
        return json_decode($result, true);
    }
    
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MyWebSocketServer()
        )
    ),
    8080
);

$server->run();