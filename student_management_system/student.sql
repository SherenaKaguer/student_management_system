
CREATE DATABASE IF NOT EXISTS student;
USE student;

CREATE TABLE Students (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_No VARCHAR(50) UNIQUE NOT NULL,
    Full_Name VARCHAR(255) UNIQUE NOT NULL,
    Email VARCHAR(100),
    Course VARCHAR(255),
    Academic_Year VARCHAR(50),
    Student_Status ENUM('Active', 'INACTIVE', 'GRADUATE')
);

CREATE TABLE Subjects (
    Subject_ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) UNIQUE NOT NULL,
    Title VARCHAR(255) UNIQUE NOT NULL,
    Units INT NOT NULL,
    Department VARCHAR(255),
    Grades_Record INT DEFAULT 0 
);

CREATE TABLE Grades (
    Grade_ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_No VARCHAR(50),
    Full_Name VARCHAR(255),
    Title VARCHAR(255),
    Grades_Record VARCHAR(5),
    Semester VARCHAR(50),
    Academic_Year VARCHAR(50),
    Remarks VARCHAR(255),
    Actions VARCHAR(255),
    CONSTRAINT fk_gr_student_no FOREIGN KEY (Student_No) REFERENCES Students(Student_No),
    CONSTRAINT fk_gr_title FOREIGN KEY (Title) REFERENCES Subjects(Title)
);

CREATE TABLE Records (
    Record_ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50),
    Title VARCHAR(255),
    Units INT,
    Enrolled VARCHAR(50),
    Avg_Grade VARCHAR(5),
    Passed VARCHAR(50),
    Failed VARCHAR(50),
    Pass_Rate VARCHAR(50),
    CONSTRAINT fk_re_code FOREIGN KEY (Code) REFERENCES Subjects(Code),
    CONSTRAINT fk_re_title FOREIGN KEY (Title) REFERENCES Subjects(Title)
);

INSERT INTO Students (Student_No, Full_Name, Email, Course, Academic_Year, Student_Status) VALUES 
('2024-001', 'Alice Johnson', 'alice@school.edu', 'BS Computer Science', '2', 'Active'),
('2024-002', 'Bob Martinez', 'bob@school.edu', 'BS Information Technology', '1', 'Active'),
('2024-003', 'Carol White', 'carol@school.edu', 'BS Computer Engineering', '3', 'Active'),
('2024-004', 'David Kim', 'david@school.edu', 'BS Computer Science', '2', 'Active'),
('2024-005', 'Eva Cruz', 'eva@school.edu', 'BS Information Systems', '4', 'GRADUATE');

INSERT INTO Subjects (Code, Title, Units, Department, Grades_Record) VALUES 
('CS101', 'Introduction to Computing', 3, 'CS Dept', 0),
('CS201', 'Data Structures and Algorithms', 3, 'CS Dept', 0),
('CS301', 'Database Management Systems', 3, 'CS Dept', 0),
('IT101', 'Introduction to Information Technology', 3, 'IT Dept', 0),
('IT201', 'Network Fundamentals', 3, 'IT Dept', 0),
('IT301', 'Cybersecurity Basics', 3, 'IT Dept', 0),
('CE101', 'Introduction to Computer Engineering', 3, 'CE Dept', 0);

INSERT INTO Grades (Student_No, Full_Name, Title, Grades_Record, Semester, Academic_Year, Remarks, Actions) VALUES 
('2024-001', 'Alice Johnson', 'Introduction to Computing', 'A', 'Fall 2024', '2', 'Excellent performance', 'None'),
('2024-002', 'Bob Martinez', 'Data Structures and Algorithms', 'B', 'Fall 2024', '1', 'Good performance', 'None'),
('2024-003', 'Carol White', 'Database Management Systems', 'A', 'Fall 2024', '3', 'Excellent performance', 'None'),
('2024-004', 'David Kim', 'Introduction to Information Technology', 'C', 'Fall 2024', '2', 'Satisfactory performance', 'None'),
('2024-005', 'Eva Cruz', 'Network Fundamentals', 'B', 'Fall 2024', '4', 'Good performance', 'None');

INSERT INTO Records (Code, Title, Units, Enrolled, Avg_Grade, Passed, Failed, Pass_Rate) VALUES 
('CS101', 'Introduction to Computing', 3, '50', 'B+', '45', '5', '90%'),
('CS201', 'Data Structures and Algorithms', 3, '40', 'B', '35', '5', '87.5%'),
('CS301', 'Database Management Systems', 3, '30', 'A-', '28', '2', '93.3%'),
('IT101', 'Introduction to Information Technology', 3, '60', 'B', '55', '5', '91.7%'),
('IT201', 'Network Fundamentals', 3, '45', 'B-', '40', '5', '88.9%'),
('IT301', 'Cybersecurity Basics', 3, '35', 'A', '33', '2', '94.3%'),
('CE101', 'Introduction to Computer Engineering', 3, '25', 'A-', '23', '2', '92%');
 



