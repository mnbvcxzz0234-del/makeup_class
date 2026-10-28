CREATE TABLE students(id SERIAL PRIMARY KEY, name TEXT NOT NULL);
CREATE TABLE credits(student_id INT PRIMARY KEY REFERENCES students(id) ON DELETE CASCADE, balance NUMERIC NOT NULL DEFAULT 0);
CREATE TABLE makeup_rules(id SERIAL PRIMARY KEY, course TEXT UNIQUE NOT NULL, notice_hours INT NOT NULL DEFAULT 24, max_per_month INT NOT NULL DEFAULT 2, window_days INT NOT NULL DEFAULT 30, cost_credits NUMERIC NOT NULL DEFAULT 1);
CREATE TABLE absences(id SERIAL PRIMARY KEY, student_id INT NOT NULL REFERENCES students(id) ON DELETE CASCADE, course TEXT NOT NULL, class_date DATE NOT NULL, class_time TIME NOT NULL, reason TEXT, eligible BOOLEAN NOT NULL, note TEXT, status TEXT NOT NULL DEFAULT 'open', reported_at TIMESTAMP NOT NULL DEFAULT now());
CREATE TABLE makeup_bookings(id SERIAL PRIMARY KEY, absence_id INT NOT NULL REFERENCES absences(id), student_id INT NOT NULL REFERENCES students(id), slot_date DATE NOT NULL, slot_time TIME NOT NULL, status TEXT NOT NULL DEFAULT 'booked', credit_used NUMERIC NOT NULL DEFAULT 0);
INSERT INTO makeup_rules(course,notice_hours,max_per_month,window_days,cost_credits) VALUES('*',24,2,30,1);
