CREATE DATABASE alpha_pizza_db;
USE alpha_pizza_db;

-- Create table for employees --
CREATE TABLE EMPLOYEE (
  EmployeeID INT NOT NULL AUTO_INCREMENT,
  StartDate DATE NOT NULL,
  SSN INT NOT NULL,
  FirstName VARCHAR(255) NOT NULL,
  LastName VARCHAR(255) NOT NULL,
  Email VARCHAR(255) NOT NULL CHECK (Email like '%_@__%.__%'),
  DOB DATE NOT NULL,
  Salary DECIMAL(8,2) NOT NULL,
  -- first employee in the system should not have a supervisor so supervisor id can be null -- 
  SupervisorID INT, 
  Position VARCHAR(255) NOT NULL DEFAULT 'Unassigned',
  Department VARCHAR(255) NOT NULL DEFAULT 'Unassigned',

  PRIMARY KEY (EmployeeIemployeeD),

  CONSTRAINT SUPER_EMPLOYEE_FK
    FOREIGN KEY (SupervisorID) REFERENCES EMPLOYEE(EmployeeID)
    ON DELETE RESTRICT ON UPDATE CASCADE
);
-- Create table for customers --
CREATE TABLE CUSTOMER
(
		CustomerID 	INT 			NOT NULL,
		FirstName	VARCHAR(255)	NOT NULL,
		LastName	VARCHAR(255)	NOT NULL,
		Phone		VARCHAR(15)		NOT NULL,
		Email		VARCHAR(255)	NOT NULL,
			CHECK (Email like '%_@__%.__%'),
		Address	VARCHAR(255)	NOT NULL,

		CONSTRAINT CUST_PK
			PRIMARY KEY(CustomerID)
);

-- This table indicates which customer an order belong to --
CREATE TABLE ORDERPLACEDBY
	(
		OrderID	INT			NOT NULL,
		CustomerID 	INT 			NOT NULL,
		

		CONSTRAINT ORDERPLACEDBY_PK
			PRIMARY KEY(OrderID),
		CONSTRAINT ORDER_CUST_FK
			FOREIGN KEY(CustomerID) REFERENCES CUSTOMER(CustomerID)
				ON DELETE CASCADE	ON UPDATE CASCADE
);

-- Create table for general information about an order --
CREATE TABLE ORDEROVERVIEW
( 
	OrderID 	INT			NOT NULL,
	TotalAmount	DECIMAL(8,2)		NOT NULL,
	OrderStatus 	VARCHAR(30)	NOT NULL,
	OrderDate	DATETIME		NOT NULL,
	
	CONSTRAINT ORDEROVERVIEW_PK 
		PRIMARY KEY(OrderID)
);

-- Create table for item info --
CREATE TABLE ITEM
(
	ItemID		INT			NOT NULL,
	isAvailable	BOOL			NOT NULL,
	Price		DECIMAL(6,2) 	NOT NULL,
	ItemName	VARCHAR(100) 	NOT NULL,
	Category 	VARCHAR(50)	NOT NULL,
	
	CONSTRAINT ITEM_PK
		PRIMARY KEY(ItemID)
);

-- Create table for details about an order --
CREATE TABLE ORDER_DETAIL 
(
    OrderDetailID    	INT            NOT NULL,
    OrderID		INT            NOT NULL,

    CONSTRAINT ORDER_DETAIL_PK 
PRIMARY KEY (OrderDetailID),
    CONSTRAINT ORDER_DETAIL_ORDER_FK
        	FOREIGN KEY (OrderID) REFERENCES ORDEROVERVIEW(OrderID)
        		ON DELETE CASCADE 	ON UPDATE CASCADE
);

-- This table contains information about item qty, subtotal, and item ID on each order -- 
CREATE TABLE ORDERDETAIL_ITEM 
(
    OrderDetailID	INT            		NOT NULL,
    Subtotal         	DECIMAL(8,2)   	NOT NULL,
    Quantity         	INT            		NOT NULL,
    ItemID           	INT            		NOT NULL,
    CONSTRAINT ORDERDETAIL_ITEM_PK 
PRIMARY KEY (OrderDetailID),
    CONSTRAINT ORDERDETAILITEM_DETAIL_FK
        	FOREIGN KEY (OrderDetailID) REFERENCES ORDER_DETAIL(OrderDetailID)
        		ON DELETE CASCADE 	ON UPDATE CASCADE,
    CONSTRAINT ORDERDETAIL_ITEM_ITEM_FK
        	FOREIGN KEY (ItemID) REFERENCES ITEM(ItemID)
        		ON DELETE RESTRICT 	ON UPDATE CASCADE
);

-- Create table for ingredient -- 
CREATE TABLE INGREDIENT
(
IngredientID		INT			NOT NULL,
	ReorderLevel		DECIMAL(8,2)		NOT NULL,
	IngredientName	VARCHAR(100)	NOT NULL,
	QtyInStock		DECIMAL (8,2)	NOT NULL,
	Unit			VARCHAR(20)	NOT NULL,
	
	CONSTRAINT INGREDIENT_PK
		PRIMARY KEY(IngredientID)
);

-- Cretae table for supplier
CREATE TABLE SUPPLIER
(
	SupplierID 		INT 			NOT NULL,
	SupplierName		VARCHAR(255)	NOT NULL,
	Phone			VARCHAR(15)	NOT NULL,
	Email			VARCHAR(255)	NOT NULL
		CHECK (Email like '%_@__%.__%'),
	Address		VARCHAR(255)	NOT NULL,
	ContactPerson	VARCHAR(255)	NOT NULL,
	
	CONSTRAINT SUPPLY_PK
		PRIMARY KEY(SupplierID)
);

-- -- This table tracks which employee made each part of an order and timestamp --
CREATE TABLE MAKESBY 
(
EmployeeID       INT            	NOT NULL,
    	OrderDetailID    INT            	NOT NULL,
    	TimeStamp        DATETIME   NOT NULL,

    	CONSTRAINT MAKESBY_PK 
PRIMARY KEY (EmployeeID, OrderDetailID),
    	CONSTRAINT MAKESBY_EMPLOYEE_FK
        		FOREIGN KEY (EmployeeID) REFERENCES EMPLOYEE(EmployeeID)
        			ON DELETE RESTRICT 	ON UPDATE CASCADE,
    	CONSTRAINT MAKESBY_DETAIL_FK
        		FOREIGN KEY (OrderDetailID) REFERENCES ORDER_DETAIL(OrderDetailID)
        			ON DELETE RESTRICT 	ON UPDATE CASCADE
);

-- This table tracks what ingredients are contained by what items --
CREATE TABLE CONTAIN
(
ItemID		INT		NOT NULL,
IngredientID	INT		NOT NULL,
Qty		INT		NOT NULL,
	
CONSTRAINT CONTAIN_PK
  	PRIMARY KEY(ItemID, IngredientID),
CONSTRAINT CONTAIN_ITEM_FK
	FOREIGN KEY(ItemID) REFERENCES ITEM(ItemID)
			ON DELETE CASCADE		ON UPDATE CASCADE,
	CONSTRAINT CONTAIN_INGREDIENT_FK
		FOREIGN KEY(IngredientID) REFERENCES INGREDIENT(IngredientID)
			ON DELETE RESTRICT		ON UPDATE CASCADE
);

-- This table indicates ingredient(s) being filled by supplier(s) --
CREATE TABLE FILLEDBY
(
SupplierID	INT		NOT NULL,
IngredientID	INT		NOT NULL,

CONSTRAINT FILLEDBY_PK
  		PRIMARY KEY(SupplierID, IngredientID),
CONSTRAINT FILL_SUPPLY_FK
	FOREIGN KEY(SupplierID) REFERENCES SUPPLIER(SupplierID)
		ON DELETE CASCADE		ON UPDATE CASCADE,
CONSTRAINT FILL_INGREDIENT_FK
	FOREIGN KEY(IngredientID) REFERENCES INGREDIENT(IngredientID)
		ON DELETE CASCADE		ON UPDATE CASCADE
);





