<?php
// =============================================
// SMART EduTRACK - Backend API (Database Ready)
// =============================================
// Single file handling all API endpoints and database operations
// Assumes database 'student' is already created with all tables
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database Configuration - Updated to use 'student' database
define('DB_HOST', 'localhost');
define('DB_NAME', 'student');
define('DB_USER', 'root');
define('DB_PASS', '');

// =============================================
// DATABASE CONNECTION
// =============================================
function getConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            sendError('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

// =============================================
// HELPER FUNCTIONS
// =============================================
function sendResponse($data) {
    echo json_encode(['success' => true] + $data);
    exit;
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// =============================================
// API HANDLERS
// =============================================
function handleDashboard($pdo) {
    // Total students
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    
    // Active students
    $activeStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'ACTIVE'")->fetchColumn();
    
    // Total subjects
    $totalSubjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    
    // Grade records
    $gradeRecords = $pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
    
    // Average grade
    $avgGrade = $pdo->query("SELECT AVG(grade_value) FROM grades WHERE grade_value IS NOT NULL")->fetchColumn();
    
    // Recent students
    $recentStudents = $pdo->query("
        SELECT student_no, name, course, status 
        FROM students 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Grade distribution
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN grade_value >= 90 THEN 1 ELSE 0 END) AS A_count,
            SUM(CASE WHEN grade_value >= 80 AND grade_value < 90 THEN 1 ELSE 0 END) AS B_count,
            SUM(CASE WHEN grade_value >= 70 AND grade_value < 80 THEN 1 ELSE 0 END) AS C_count,
            SUM(CASE WHEN grade_value < 70 THEN 1 ELSE 0 END) AS D_count
        FROM grades
    ");
    $counts = $stmt->fetch();
    $total = $counts['total'] ?: 1;
    
    $gradeDistribution = [
        ['grade' => 'A', 'range' => '90–100', 'percent' => round(($counts['A_count'] / $total) * 100)],
        ['grade' => 'B', 'range' => '80–89', 'percent' => round(($counts['B_count'] / $total) * 100)],
        ['grade' => 'C', 'range' => '70–79', 'percent' => round(($counts['C_count'] / $total) * 100)],
        ['grade' => 'D', 'range' => '< 70', 'percent' => round(($counts['D_count'] / $total) * 100)]
    ];
    
    sendResponse([
        'totalStudents' => (int)$totalStudents,
        'activeStudents' => (int)$activeStudents,
        'subjects' => (int)$totalSubjects,
        'gradeRecords' => (int)$gradeRecords,
        'avgGrade' => $avgGrade ? round($avgGrade, 2) : null,
        'recentStudents' => $recentStudents,
        'gradeDistribution' => $gradeDistribution
    ]);
}

function handleGetStudents($pdo) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        sendResponse(['data' => $data]);
    } else {
        $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
        $data = $stmt->fetchAll();
        sendResponse(['data' => $data]);
    }
}

function handleAddStudent($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $required = ['student_no', 'name', 'course'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            sendError("$field is required");
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO students (student_no, name, course, status, email, phone, enrollment_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $input['student_no'],
        $input['name'],
        $input['course'],
        $input['status'] ?? 'ACTIVE',
        $input['email'] ?? null,
        $input['phone'] ?? null,
        date('Y-m-d')
    ]);
    
    sendResponse(['id' => $pdo->lastInsertId(), 'message' => 'Student added successfully']);
}

function handleUpdateStudent($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $id = $input['id'] ?? null;
    if (!$id) {
        sendError('Student ID is required');
    }
    
    $stmt = $pdo->prepare("
        UPDATE students SET 
            student_no = ?, name = ?, course = ?, status = ?, 
            email = ?, phone = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $input['student_no'],
        $input['name'],
        $input['course'],
        $input['status'] ?? 'ACTIVE',
        $input['email'] ?? null,
        $input['phone'] ?? null,
        $id
    ]);
    
    sendResponse(['message' => 'Student updated successfully']);
}

function handleDeleteStudent($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $id = $input['id'] ?? $_GET['id'] ?? null;
    if (!$id) {
        sendError('Student ID is required');
    }
    
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    
    sendResponse(['message' => 'Student deleted successfully']);
}

function handleGetSubjects($pdo) {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY subject_code");
    sendResponse(['data' => $stmt->fetchAll()]);
}

function handleGetGrades($pdo) {
    $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
    
    if ($studentId) {
        $stmt = $pdo->prepare("
            SELECT g.*, s.subject_name, s.subject_code 
            FROM grades g
            JOIN subjects s ON g.subject_id = s.id
            WHERE g.student_id = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$studentId]);
    } else {
        $stmt = $pdo->query("
            SELECT g.*, st.name as student_name, s.subject_name
            FROM grades g
            JOIN students st ON g.student_id = st.id
            JOIN subjects s ON g.subject_id = s.id
            ORDER BY g.created_at DESC
            LIMIT 50
        ");
    }
    
    sendResponse(['data' => $stmt->fetchAll()]);
}

// =============================================
// ROUTER
// =============================================
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = getConnection();
    
    switch ($action) {
        case 'dashboard':
            handleDashboard($pdo);
            break;
            
        case 'get_students':
            handleGetStudents($pdo);
            break;
            
        case 'add_student':
            handleAddStudent($pdo);
            break;
            
        case 'update_student':
            handleUpdateStudent($pdo);
            break;
            
        case 'delete_student':
            handleDeleteStudent($pdo);
            break;
            
        case 'get_subjects':
            handleGetSubjects($pdo);
            break;
            
        case 'get_grades':
            handleGetGrades($pdo);
            break;
            
        default:
            sendError('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
?>