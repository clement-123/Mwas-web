IF DB_ID(N'car-mwas') IS NULL
BEGIN
CREATE DATABASE [car-mwas];
END
GO
USE [car-mwas];
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='cars' AND xtype='U')
BEGIN
    CREATE TABLE cars (
        id INT IDENTITY(1,1) PRIMARY KEY,
        make VARCHAR(50),
        model VARCHAR(50),
        year INT,
        price DECIMAL(10,2),
        image VARCHAR(255),
        description TEXT,
        created_at DATETIME DEFAULT GETDATE()
    );
END

INSERT INTO cars (make, model, year, price, image, description) VALUES
('Toyota', 'Axio', 2012, 850000.00, 'axio.jpg', 'Fuel efficient, low mileage, excellent condition.'),
('Volkswagen', 'GTI', 2016, 1200000.00, 'GTI.jpg', 'Spacious interior, great for families, well-maintained.'),
('Suzuki', 'Swift Sport', 2011, 780000.00, 'swift.jpg', 'Sporty design, powerful engine, low mileage.'),
('Toyota', 'Hiace', 2019, 2000000.00, 'hiace.jpg', 'Spacious vehicle, autopilot features, long range.'),
('BMW', 'X6 Series', 2018, 8500000.00, 'x6.jpg', 'Luxury SUV, premium features, excellent performance.'),
('Land Rover', 'Range Rover Velar', 2020, 10000000.00, 'range.jpg', 'Luxury SUV, advanced technology, off-road capabilities.'),
('Toyota', 'Vitz', 2010, 500000.00, 'Toyota-vitz.jpg', 'Great for a small family, easy to maintain, low mileage.'),
('Toyota', 'Fortuner', 2017, 4500000.00, 'fortuner.jpg', 'Luxury SUV, comfortable ride, advanced safety features.'),
('Toyota', 'Harrier', 2017, 2500000.00, 'HARRIER.jpg', 'Spacious SUV, great for families, reliable performance.'),
('Toyota', 'Prado TX', 2017, 3500000.00, 'prado tx.jpg', 'Luxury SUV, off-road capabilities, spacious interior.'),
('Nissan', 'X-Trail', 2017, 1800000.00, 'X-trail.jpg', 'Compact SUV, fuel efficient, great for city driving.'),
('Mercedes-Benz', 'E-Class', 2016, 3270000.00, 'e 200.jpg', 'Luxury sedan, advanced technology, smooth ride.'),
('Toyota', 'Corolla (E100)', 2001, 200000.00, '100.jpg', 'Reliable sedan, fuel efficient, low maintenance costs.'),
('Honda', 'Vezel', 2016, 2200000.00, 'vezel.jpeg', 'Spacious hatch-back, advanced safety features, comfortable ride.'),
('Mazda', 'CX-5', 2018, 1800000.00, 'cx5.jpg', 'Compact SUV, stylish design, great handling.');

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='spareparts' AND xtype='U')
BEGIN
    CREATE TABLE spareparts (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name VARCHAR(100),
        price DECIMAL(10,2),
        image VARCHAR(255),
        description TEXT,
        created_at DATETIME DEFAULT GETDATE()
    );
END

INSERT INTO spareparts (name, price, image, description) VALUES
('2NZ-FE Engine', 150000.00, '2NZ-FE.jpg', 'High-performance engine for various models.'),
('Break pads', 8000.00, 'break pad.jpeg', 'breakpads fo enhanced safety'),
('Tyres', 10000.00, 'tyres.jpg', 'Durable tyres for all weather conditions.'),
('12V Car Battery', 15000.00, 'battery.jpg', 'Reliable battery for long-lasting performance.'),
('Nissan CVT transmission', 5000.00, 'NissanCVT.jpg', 'Smooth shifting transmission for optimal performance.'),
('Suspension System', 50000.00, 'Suspension.jpg', 'Advanced Suspension System for a comfortable ride.'),
('Spark Plugs', 2000.00, 'spark_plugs.jpg', 'High-performance spark plugs for better ignition.'),
('Brake Discs', 12000.00, 'brake_discs.jpg', 'Durable brake discs for enhanced braking performance.'),
('Clutch Kit', 12000.00, 'clutch_kit.jpg', 'Complete clutch kit for smooth gear shifting.'),
('radiator', 25000.00, 'radiator.jpg', 'Efficient radiator for efficient cooling.'),
('Exhaust System', 40000.00, 'exhaust.jpg', 'High-performance exhaust system for better airflow.'),
('Alternator', 20000.00, 'alternator.jpg', 'Reliable alternator for consistent power supply.'),
('Starter Motor', 18000.00, 'Starter.jpg', 'Efficient starter motor for quick engine start.'),
('Fuel Pump', 22000.00, 'fuelpump.jpg', 'High-performance fuel pump for optimal fuel delivery.'),
('Air Filter', 5000.00, 'air filter.jpg', 'High-quality air filter for better engine performance.'),
('Spark Plugs', 3000.00, 'spark plugs.jpg', 'Durable spark plugs for efficient combustion.'),
('Timming Belt', 10000.00, 'timing belt.jpg', 'High quality timing belt for precise engine timing.'),
('Oil Filter', 4000.00, 'oil filter.jpg', 'High-efficiency oil filter for clean engine oil.'),
('Clutch Kit', 12000.00, 'clutch_kit.jpg', 'Durable clutch kit for smooth gear shifting.');

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='users' AND xtype='U')
BEGIN
    CREATE TABLE users (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        email VARCHAR(100) UNIQUE,
        created_at DATETIME DEFAULT GETDATE()
    );
END

INSERT INTO users (username, password, email) VALUES
('admin', 'clement@38584098','clemojones@gmail.com'),
('user1', 'password123', 'puritymuthike@gmail.com');



