
CREATE DATABASE IF NOT EXISTS e_commerce;
USE e_commerce;  

CREATE TABLE IF NOT EXISTS admin (
    admin_id INT NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (admin_id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS user (
    user_id INT NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    birthday_date date,
    email VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    points INT DEFAULT 0 ,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
)ENGINE = InnoDB;
ALTER TABLE user AUTO_INCREMENT = 3000; 

CREATE TABLE IF NOT EXISTS country (
    country_id INT NOT NULL AUTO_INCREMENT,
    country_name VARCHAR(50),
    PRIMARY KEY(country_id)    
) ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS address (
    address_id INT NOT NULL AUTO_INCREMENT,
    ext_number INT UNSIGNED,
    int_number INT UNSIGNED,
    city VARCHAR(50),
    state VARCHAR(50),
    street VARCHAR(50),
    country_id INT,
    PRIMARY KEY(address_id),
    CONSTRAINT address_country_FK FOREIGN KEY (country_id)
    REFERENCES country (country_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
) ENGINE InnoDB;
ALTER TABLE address AUTO_INCREMENT = 5000;

ALTER TABLE address 
ADD COLUMN cp VARCHAR(10) AFTER street;

CREATE TABLE IF NOT EXISTS user_address(
    user_id INT NOT NULL,
    address_id INT NOT NULL,
    is_default BOOLEAN,
    CONSTRAINT userAddres_user_FK FOREIGN KEY (user_id) 
    REFERENCES user(user_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT userAddres_address_FK FOREIGN KEY (address_id) 
    REFERENCES address(address_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
) ENGINE InnoDB;

 CREATE TABLE IF NOT EXISTS size_category (
    category_id INT AUTO_INCREMENT,
    category_name VARCHAR(50),
    PRIMARY KEY (category_id)
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS size (
    size_id INT AUTO_INCREMENT,
    size_category_id INT,
    size_name VARCHAR(50),
    sort_order TINYINT,
    PRIMARY KEY (size_id),
    CONSTRAINT size_sizeCategory_FK FOREIGN KEY (size_category_id) 
    REFERENCES size_category (category_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS category (
    category_id INT AUTO_INCREMENT,
    category_name VARCHAR(50),
    category_description TEXT,
    category_img VARCHAR(100),
    size_category_id INT,
    parent_category_id INT,
    PRIMARY KEY (category_id),
    CONSTRAINT category_sizeCategory_FK FOREIGN KEY (size_category_id)
    REFERENCES size_category(category_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT parentCategory_category_FK FOREIGN KEY (parent_category_id)
    REFERENCES category(category_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
) ENGINE InnoDB;
CREATE TABLE IF NOT EXISTS brand (
    brand_id INT AUTO_INCREMENT,
    brand_name VARCHAR(50),
    brand_description TEXT,
    PRIMARY KEY (brand_id)
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS product (
    product_id INT NOT NULL AUTO_INCREMENT,
    product_name VARCHAR(50),
    brand_id INT,
    category_id INT,
    product_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    CONSTRAINT product_brand_FK FOREIGN KEY (brand_id) 
    REFERENCES brand (brand_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
    CONSTRAINT product_category_FK FOREIGN KEY (category_id) 
    REFERENCES category(category_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
) ENGINE InnoDB;
ALTER TABLE product AUTO_INCREMENT = 6000;

CREATE TABLE IF NOT EXISTS color (
    color_id INT AUTO_INCREMENT,
    color_name VARCHAR(50),
    PRIMARY KEY (color_id)
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS product_item (
    product_item_id INT AUTO_INCREMENT,
    product_id INT,
    color_id INT,
    original_price DECIMAL(10,2),
    sale_price DECIMAL(10,2),
    product_code VARCHAR(20) UNIQUE, 
    PRIMARY KEY(product_item_id), 
    CONSTRAINT productItem_product_FK FOREIGN KEY (product_id) 
    REFERENCES product(product_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT productItem_color_FK FOREIGN KEY (color_id) 
    REFERENCES color(color_id) 
    ON DELETE CASCADE
    ON UPDATE CASCADE
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS product_img (
    img_id INT AUTO_INCREMENT,
    product_item_id INT,
    img_filename VARCHAR(100),
    PRIMARY KEY (img_id),
    CONSTRAINT productItem_img_FK FOREIGN KEY (product_item_id) 
    REFERENCES product_item (product_item_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS product_variation (
    variation_id INT AUTO_INCREMENT,
    product_item_id INT,
    size_id INT,
    quantity_stock INT,
    PRIMARY KEY (variation_id),
    CONSTRAINT productVaration_productItem_FK FOREIGN KEY (product_item_id) 
    REFERENCES product_item (product_item_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT productVariation_size_FK FOREIGN KEY(size_id) 
    REFERENCES size (size_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB;
    
CREATE TABLE IF NOT EXISTS attribute_type (
    attribute_type_id INT AUTO_INCREMENT,
    attribute_name VARCHAR(50),
    PRIMARY KEY (attribute_type_id)
) ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS attribute_option (
    attribute_option_id INT AUTO_INCREMENT,
    attribute_option_name VARCHAR(50),
    attribute_type_id INT,
    PRIMARY KEY (attribute_option_id),
    CONSTRAINT attributeOption_attributeType_FK FOREIGN KEY (attribute_type_id) 
    REFERENCES attribute_type (attribute_type_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS product_attribute (
    product_id INT,
    attribute_option_id INT,
    CONSTRAINT productAttribute_product_FK FOREIGN KEY(product_id) 
    REFERENCES product(product_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT productAttribute_attributeOption_FK FOREIGN KEY (attribute_option_id) 
    REFERENCES attribute_option (attribute_option_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)ENGINE InnoDB;
    
CREATE TABLE IF NOT EXISTS promotion (
    promo_id INT AUTO_INCREMENT,
    promo_name VARCHAR(50),
    promo_description TEXT,
    discount_porcent DECIMAL(10,2),
    start_date DATETIME,
    end_date DATETIME,
    PRIMARY KEY (promo_id)
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS promo_category (
    category_id INT,
    promo_id INT,
    CONSTRAINT promoCategory_category_FK FOREIGN KEY (category_id) 
    REFERENCES category(category_id) 
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT promoCategory_promotion_FK FOREIGN KEY (promo_id)
    REFERENCES promotion(promo_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS payment_type (
    payment_type_id INT AUTO_INCREMENT,
    payment_type_name VARCHAR(50),
    PRIMARY KEY(payment_type_id)
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS payment_method (
    method_id INT AUTO_INCREMENT,
    user_id INT,
    payment_type_id INT,
    card_number VARCHAR(20),
    expiry_date VARCHAR(10),
    is_default BOOLEAN,
    PRIMARY KEY (method_id),
    CONSTRAINT paymentMethod_user_FK FOREIGN KEY (user_id) 
    REFERENCES user(user_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT paymentMethod_paymentType_FK FOREIGN KEY (payment_type_id)
    REFERENCES payment_type(payment_type_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS order_status(
    status_id INT AUTO_INCREMENT,
    status_name VARCHAR(50),
    PRIMARY KEY(status_id)
)ENGINE InnoDB;

CREATE TABLE IF NOT EXISTS user_order (
    user_order_id INT AUTO_INCREMENT,
    user_id INT,
    order_date DATE,
    payment_method_id INT,
    address_id INT,
    order_status_id INT,
    order_total DECIMAL(10,2),
    PRIMARY KEY(user_order_id),
    CONSTRAINT userOrder_user_FK FOREIGN KEY(user_id)
    REFERENCES user(user_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT userOrder_paymentMethod_FK FOREIGN KEY (payment_method_id) 
    REFERENCES payment_method(method_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT userOrder_address_FK FOREIGN KEY (address_id)
    REFERENCES address (address_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT userOrder_orderStatus_FK FOREIGN KEY (order_status_id) 
    REFERENCES order_status (status_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
)ENGINE InnoDB;
ALTER TABLE user_order AUTO_INCREMENT = 4000;

CREATE TABLE IF NOT EXISTS order_item (
    order_item_id INT AUTO_INCREMENT,
    product_item_id INT,
    user_order_id INT,
    price DECIMAL(10,2),
    quantity INT,
    PRIMARY KEY (order_item_id),
    CONSTRAINT orderItem_productItem_FK FOREIGN KEY (product_item_id) 
    REFERENCES product_item (product_item_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
    CONSTRAINT orderItem_userOrder_FK FOREIGN KEY(user_order_id) 
    REFERENCES user_order (user_order_id)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
)ENGINE InnoDB;
    

INSERT INTO user (user_id, first_name, last_name, email, phone, password)
VALUES (3000, 'Carlos', 'Fernandez', 'carlosf@mail.com', '+351910000001','123456'),
       (3001, 'Ana', 'Gomes', 'ana.gomes@mail.com', '+351920000002', '123456'),
       (3002, 'Miguel', 'Costa', 'miguel.costa@mail.com', '+351950000005', '123456');
       
INSERT INTO admin ( first_name, last_name, email, phone, password)
VALUES ( 'Mauricio', 'Ramirez', 'mauri_admin@mail.com', '+351910000001','123456'),
       ( 'Felipe', 'Hernandez', 'admin@mail.com','+351910009801', '$2y$10$N8c.jv1GPqePvwLyXs6Q7eDMFfJIGtne6dHGeljfz5GNO7Yi/dJOC');

INSERT INTO country (country_name)
VALUES ('Portugal'), ('Spain'), 
        ('France'), ('Germany'),
        ('Italy'), ('Hungary');

INSERT INTO address (address_id, ext_number, int_number, city, state, street, cp, country_id)
VALUES (5000, 10, NULL, 'Lisboa', 'Lisboa', 'Rua da Liberdade', '1000-123',  1),
       (5001, 21, 3, 'Porto', 'Porto', 'Av. dos Aliados', '1900-123',  1),
       (5002, 85, NULL, 'Coimbra', 'Coimbra', 'Rua da Sofia', '1800-123', 1),
       (5003, 17, NULL, 'Faro', 'Algarve', 'Av. 5 de Outubro', '1700-123', 1),
       (5004, 33, 1, 'Braga', 'Braga', 'Rua do Souto', '1600-123',1);
INSERT INTO user_address (user_id, address_id, is_default)
VALUES (3000, 5000, 1),
        (3001, 5001, 1),
        (3002, 5002, 0);
        
INSERT INTO  color (color_name) 
VALUES ('Black'), ('White'),
        ('Red'), ('Blue'),
        ('Green'), ('Yellow'), 
        ('Grey'), ('orange');
        
INSERT INTO size_category (category_name)
VALUES ('Clothing Sizes'), ('Shoe Sizes');

INSERT INTO category (category_name, category_description, size_category_id, parent_category_id)
VALUES ('Clothing', 'All wearable apparel', 1, NULL),
        ('Accessories', 'Supplementary fashion items', 1, NULL),
        ('Footwear', 'Shoes and sneakers', 2, NULL),
        ('Jeans', 'Denim pants in various styles',1, 1),
        ('Pants', 'Casual or formal pants',1, 1),
        ('Shorts', 'Various types of shorts',1, 1),
        ('T-Shirts', 'Casual cotton t-shirts',1, 1),
        ('Sweaters', 'Warm clothing for cold weather', 1,  1),
        ('Caps', 'Stylish caps and hats', 1, 2),
        ('Backpacks', 'Backpacks for daily use', 1, 2),
        ('Waist Bags', 'Small bags worn around the waist', null, 2),
        ('Sunglasses', 'Eyewear for sun protection and style', null, 2),
        ('Sneakers', 'Casual and athletic shoes', 2, 3);
 
  
INSERT INTO brand (brand_name, brand_description)
VALUES ('Nike', 'Global sportswear and footwear brand.'),
        ('Adidas', 'Leading brand in sports and lifestyle apparel.'),
        ('Puma', 'International brand offering athletic and casual footwear.'),
        ('Acuatica', 'Casual clothing for the entire family'),
        ('Wright PLC', 'The best street style'),
        ('Martinez Inc', 'Contemporary fashion company'),
        ('Rger&Adam', 'Clothing for the comfort of your daily life.'),
        ('Wolfe Garci', 'Luxury brand only for the outstanding');
                    


INSERT INTO size (size_name, size_category_id, sort_order)
VALUES ('XS', 1, 1), ('S', 1, 2),
        ('M', 1, 3), ('L', 1, 4),
        ('XL', 1, 5), ('XXL', 1, 6),
        ('36', 2, 1), ('37', 2, 2),
        ('38', 2, 3), ('39', 2, 4),
        ('40', 2, 5), ('41', 2, 6),
        ('42', 2, 7), ('43', 2, 8),
        ('44', 2, 9), ('45', 2, 10);
        
INSERT INTO attribute_type (attribute_name) 
VALUES ('Material'), ('Gender'), ('Fit'), 
        ('Style'), ('Color Group');

INSERT INTO attribute_option (attribute_option_name, attribute_type_id) 
VALUES ('Cotton', 1), ('Polyester', 1), ('Denim', 1), ('Leather', 1), ('Rubber', 1),
     ('Men', 2), ('Women', 2), ('Unisex', 2),   
     ('Regular', 3), ('Slim', 3), ('oversize', 3),
     ('Casual', 4), ('Sport', 4), ('Street', 4),
     ('Black', 5), ('White', 5), ('Blue', 5), ('Red', 5), ('Green', 5);
    
-- Jeans    4 
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES 
('Baggy jeans', 'Baggy jeans available in various shades, featuring a logo, five pockets, belt loops and a
 button and zip fly fastening. Made of 100% cotton.', 4, 4),
("Men's Trousers", "n everyday essential crafted from premium materials? It's got to be Air Jordan. 
The heavyweight denim gives these relaxed trousers a durable and structured feel. A button fly 
keeps the look traditional, while a leather brand patch with a metal Jumpman adds a luxe touch.", 3, 4),
("Adicolor Denim Firebird Joggers", "The adidas Firebird track suit has seen it all. It's been on the shoulders
of athletes and it's been on the shoulders of hip-hop stars. Now, it's ready to see what you can do. These pants stay true to their athletic roots with the iconic 
3-Stripes down the sides. But with a denim construction, they're ready for adventures off the track.", 2, 4),
("Super skinny ripped jeans", 'High-quality jeans perfect for everyday use. Model 3.', 1, 4),
('Baggy Jeans', 'Baggy jeans available in various shades, featuring a logo, five pockets, belt loops and a 
button and zip fly fastening. Made of 100% cotton.', 5, 4),
('Slim Fit Jeans', 'Slim fit jeans with faded detailing and ripped knees. 
Classic five-pocket design with zip fly and button fastening.', 6,4),
('Wide Leg Jeans', 'High-rise wide leg jeans with raw-cut hems. Clean look with minimal 
stitching for a modern silhouette.',7,4);

-- pants 5 
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES  
('Cargo Pants', 'Utility-style cargo trousers with side flap pockets and elasticated hems.
 Button and zip fastening.', 1, 5),
('Tailored Pants', 'Cropped tailored trousers with a slim fit. Side pockets and back welt pockets.', 2, 5),
('Jogger Pants', 'Comfortable jogger-style trousers with an elastic waistband and ankle cuffs.
 Soft brushed interior.', 3, 5),
 ('Slim Chinos', 'Slim-fit chino trousers with side and back pockets. Stretch fabric for comfort.', 4, 5),
('Pleated Trousers', 'High-waisted pleated trousers with a clean, modern finish. Hook and zip closure.', 5, 5);

-- Shorts 6
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES  
('Denim Shorts', 'High-waist denim shorts with raw hems and distressed details. Classic five-pocket styling.', 1, 6),
('Bermuda Shorts', 'Relaxed-fit Bermuda shorts with pleats. Mid-rise with belt loops and side pockets.',2,6),
('Sport Shorts', 'Lightweight sport shorts with drawstring waist. Moisture-wicking fabric and inner lining.',3,6),
('Cotton Shorts', 'Soft cotton shorts with elasticated waistband and drawstring. Side seam pockets.',6,6),
('Tailored Shorts', 'Tailored shorts with clean hem and discreet zip. Smart casual style.',7,6);

-- T-Shirts 7
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES  
('Tie-Dye Tee', 'Tie-dye cotton tee with dropped shoulders and oversized fit. Statement look.', 8, 7),
('Pocket T-Shirt', 'Regular fit T-shirt with chest pocket and small logo print. Soft fabric.',7,7),
('Graphic', 'Short-sleeve T-shirt with printed graphic on the front. Ribbed round neckline.', 5,7),
('Oversized', 'Oversized T-shirt made from soft cotton. Minimal print with dropped shoulders.',3,7),
('Basic T-shirt', 'Essential plain white T-shirt. Regular fit, 100% cotton, crew neck.',2,7);

-- Sweaters  8
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES
('Knit Sweater', 'Chunky knit sweater with round neckline and ribbed trims. Soft-touch yarn.', 1,8),
('Cropped Sweater', 'Cropped fit sweater with balloon sleeves. Made of a cotton blend.',2,8),
('Zipped Sweatshirt', 'Half-zip sweatshirt with front pocket. Relaxed fit and brushed lining.',3,8),
('Hooded Sweater', 'Relaxed-fit hoodie with kangaroo pocket and ribbed cuffs. Cotton blend.',6,8),
('Fine Knit Sweater', 'Lightweight fine-knit sweater with minimalist style. Slightly fitted shape.',7,8);

-- Sneakers  13
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES
('Platform Sneakers', 'Sneakers with thick platform sole and contrast heel tab. Bold and comfy.', 6,13),
('AIR jordan', 'Classic canvas sneakers with lace-up front and rubber toe cap.', 1, 13),
('Retro Sneakers', 'Vintage-inspired sneakers with contrast panels. Padded heel and durable rubber sole.',2, 13),
('Chunky Sneakers', 'Chunky sole sneakers with mesh and leather blend upper. Pull tab at the back.', 3,13),
('Minimal Sneakers', 'Low-profile sneakers with clean stitching and tonal laces. Everyday comfort.', 8, 13);

-- Caps 9
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES
('Embroidered Cap', 'Cotton cap with front embroidery and adjustable strap at the back.', 3, 9),
('Washed Baseball Cap', 'Faded effect cap with curved brim and breathable eyelets.', 5,9),
('Corduroy Cap', 'Structured cap in corduroy with contrast logo patch and buckle fastening',6,9),
('Logo Trucker Cap', 'Mesh-back trucker cap with embroidered logo and snapback closure.', 4,9),
('Flat Brim Cap', 'Street-style flat brim cap in wool blend. Adjustable fit.',8,9);

-- Backpacks 10
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES
('Classic Canvas Backpack', 'Durable canvas backpack with dual zip compartments and padded straps.',1,10),
('Roll-Top Backpack', 'Modern roll-top backpack with buckle strap and waterproof base.',2,10),
('Urban Backpack', 'Minimalist backpack with large main compartment and padded laptop sleeve.',4,10),
('Foldover Backpack', 'Backpack with top foldover flap, magnetic closure, and adjustable straps.',5,10),
('Mini Backpack', 'Compact backpack with double zip and front pocket. Ideal for essentials.',7,10);

-- Waist Bags 11
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES
('Utility Waist Bag', 'Functional waist bag with multiple compartments and adjustable strap.', 7,11),
('Nylon Belt Bag', 'Lightweight belt bag in nylon with zip closure and inner pocket.',6,11),
('Street Waist Pack', 'Urban-style waist pack with reflective details and clip fastening.',5,11),
('Compact Hip Pack', 'Slim hip pack with front zip and adjustable clip belt.',4,11),
('Eco Waist Bag', 'Waist bag made from recycled materials. Lightweight and eco-friendly.',3,11);

 -- Sunglasses 12  
INSERT INTO product (product_name, product_description, brand_id, category_id) VALUES
('Cat-Eye Sunglasses', 'Trendy cat-eye frames with tinted lenses and glossy finish.',1,12),
('Aviator Sunglasses', 'Classic aviator-style sunglasses with thin gold-tone metal frame.',2,12),
('Retro Square Sunglasses', 'Bold square-frame sunglasses with dark lenses and gold-tone arms.',3,12),
('Round Metal Sunglasses', 'Classic round frame with metal bridge and UV protection.',4,12),
('Sport Shield Sunglasses', 'Wraparound sport-style sunglasses with mirrored lenses and nose grip.',8,12);

INSERT INTO promotion (promo_name, promo_description, discount_porcent, start_date, end_date) VALUES
('Mid-Season Sale', 'Up to 30% off on selected items for a limited time.', 30.00, '2025-06-01 00:00:00', '2025-07-15 23:59:59'),
('Summer Essentials', 'Enjoy 15% off summer wear like shorts and t-shirts.', 15.00, '2025-06-10 00:00:00', '2025-08-01 23:59:59'),
('Accessories Deal', 'Buy 2 accessories and get 20% off.', 20.00, '2025-06-01 00:00:00', '2025-08-20 23:59:59'),
('Sneaker Week', 'Exclusive 25% off all sneakers.', 25.00, '2025-06-05 00:00:00', '2025-08-12 23:59:59');



INSERT INTO promo_category (category_id, promo_id) 
VALUES (4, 1), (5, 1), (6, 1),
        (7, 2), (8, 2), (9, 3), 
        (12, 3), (10, 3), (11, 3),(13, 4);

INSERT INTO payment_type (payment_type_name)
VALUES ('Visa'), ('MasterCard'), ('PayPal'), ('Missing points');



INSERT INTO payment_method (user_id, payment_type_id, card_number, expiry_date, is_default)
VALUES
(3000, 1, '4111111111111111', '12/28', TRUE),
(3001, 2, '5500000000000004', '09/27', TRUE);


INSERT INTO product_item (product_id, color_id, original_price, sale_price, product_code) VALUES
(6000, 1, 41.48, 32.14, 'PDR6000'),
(6001, 2, 71.05, 58.53, 'PDR6001'),
(6002, 1, 54.87, 45.34, 'PDR6002'),
(6003, 1, 35.08, 25.53, 'PDR6003'),
(6004, 5, 69.98, 55.87, 'PDR6004'),
(6005, 1, 70.72, 63.41, 'PDR6005'),
(6006, 5, 52.57, 39.56, 'PDR6006'),
(6007, 4, 39.20, 28.83, 'PDR6007'),
(6008, 5, 66.68, 56.84, 'PDR6008'),
(6009, 1, 64.83, 46.88, 'PDR6009'),
  (6010, 2, 49.99, 39.99, 'PDR6010'),
  (6011, 3, 59.99, 44.99, 'PDR6011'),
  (6012, 4, 69.99, 54.99, 'PDR6012'),
  (6013, 5, 79.99, 64.99, 'PDR6013'),
  (6014, 6, 89.99, 74.99, 'PDR6014'),
  (6015, 7, 99.99, 84.99, 'PDR6015'),
  (6016, 1, 109.99, 94.99, 'PDR6016'),
  (6017, 2, 119.99, 104.99, 'PDR6017'),
  (6018, 3, 129.99, 110.99, 'PDR6018'),
  (6019, 4, 78.99, 58.99, 'PDR6019'),
  (6020, 5, 67.99, 56.99, 'PDR6020'),
  (6021, 6, 56.99, 45.99, 'PDR6021'),
  (6022, 7, 45.99, 30.99, 'PDR6022'),
  (6023, 1, 45.99, 37.99, 'PDR6023'),
  (6024, 2, 46.99, 35.99, 'PDR6024'),
  (6025, 3, 47.99, 36.99, 'PDR6025'),
  (6026, 4, 47.99, 38.99, 'PDR6026'),
  (6027, 5, 34.99, 21.99, 'PDR6027'),
  (6028, 6, 34.99, 22.99, 'PDR6028'),
  (6029, 7, 23.99, 11.99, 'PDR6029'),
  (6030, 1, 23.99, 12.99, 'PDR6030'),
  (6031, 2, 12.99, 7.99, 'PDR6031'),
  (6032, 3, 14.99, 8.99, 'PDR6032'),
  (6033, 4, 15.99, 10.99, 'PDR6033'),
  (6034, 5, 16.99, 11.99, 'PDR6034'),
  (6035, 6, 17.99, 12.99, 'PDR6035'),
  (6036, 7, 18.99, 11.99, 'PDR6036'),
  (6037, 1, 28.99, 19.99, 'PDR6037'),
  (6038, 2, 39.99, 23.99, 'PDR6038'),
  (6039, 3, 67.99, 54.99, 'PDR6039'),
  (6040, 4, 68.99, 54.99, 'PDR6040'),
  (6041, 5, 69.99, 56.99, 'PDR6041'),
  (6042, 6, 45.99, 36.99, 'PDR6042'),
  (6043, 7, 44.99, 36.99, 'PDR6043'),
  (6044, 1, 46.99, 36.99, 'PDR6044'),
  (6045, 2, 48.99, 35.99, 'PDR6045'),
  (6046, 3, 49.99, 34.99, 'PDR6046'),
  (6047, 4, 89.99, 56.99, 'PDR6047'),
  (6048, 5, 87.99, 67.99, 'PDR6048'),
  (6049, 6, 76.99, 32.99, 'PDR6049'),
  (6050, 7, 54.99, 33.99, 'PDR6050'),
  (6051, 7, 44.99, 34.99, 'PDR6051');

  
INSERT INTO product_img (product_item_id, img_filename) VALUES
  (1, 'baggy_black_jeans.jpg'),
  (2, 'men_trousers.jpg'),
  (3, 'adicolor_denim_firebird_joggers_black.jpg'),
  (4, 'Super-skinny-ripped-jeans.jpeg'),
  (5, 'baggy_jeans_green.webp'),
  (6, 'Slim Fit Jeans.webp'),
  (7, 'wide_leg_jeans.webp'),
  (8, 'cargo_pants.jpg'),
  (9, 'tailored_pants.webp'),
  (10, 'jogger_pants.webp'),
  (11, 'slim_chinos.webp'),
  (12, 'pleated_trousers.webp'),
  (13, 'Denim_shorts.jpg'),
  (14, 'bermuda_horts.jpg'),
  (15, 'sport_shorts.jpg'),
  (16, 'cotton_shorts.webp'),
  (17, 'tailored_shorts.webp'),
  (18, 'tie_dye_tee_tshirt.jpeg'),
  (19, 'pocket_tshirt.webp'),
  (20, 'graphic_tshirt.webp'),
  (21, 'oversized_tshirt_green.webp'),
  (22, 'basic_tshirt_yellow.webp'),
  (23, 'knit_sweater.webp'),
  (24, 'cropped_weater.webp'),
  (25, 'zipped_sweatshirt.webp'),
  (26, 'hooded_sweater_unisex.jpg'),
  (27, 'fine_knit_sweater.webp'),
  (28, 'platform_sneakers.jpg'),
  (29, 'air_jordan.jpg'),
  (30, 'retro_sneakers.jpg'),
  (31, 'chunky_neakers.webp'),
  (32, 'minimal_sneakers.jpg'),
  (33, 'embroidered_cap.jpeg'),
  (34, 'washed_baseball_ap.webp'),
  (35, 'corduroy_cap.jpg'),
  (36, 'logo_trucker_cap.webp'),
  (37, 'flat_brim_cap.webp'),
  (38, 'classic_canvas_backpack.webp'),
  (39, 'roll_top_backpack.webp'),
  (40, 'urban_backpack.jpg'),
  (41, 'foldover_backpack.webp'),
  (42, 'mini_backpack.webp'),
  (43, 'utility_waist_bag.jpg'),
  (44, 'nylon_belt_bag.webp'),
  (45, 'street_waist_pack.jpg'),
  (46, 'compact_hip_pack.jpg'),
  (47, 'eco_waist_bag.jpg'),
  (48, 'cat_eye _sunglasses.webp'),
  (49, 'aviator _sunglasses.webp'),
  (50, 'retro_square_sunglasses.jpg'),
  (51, 'round_metal_sunglasses.webp'),
  (52, 'sport_shield_sunglasses.webp');

INSERT INTO product_variation (product_item_id, size_id, quantity_stock) VALUES
  (1, 3, 16), (1, 4, 13), (1, 1, 9), (2, 1, 5), (2, 3, 20), (2, 4, 11),
  (3, 3, 14), (3, 4, 6), (3, 2, 19),  (4, 4, 19), (4, 3, 17), (4, 2, 11),
  (5, 4, 8), (5, 1, 7), (5, 3, 6),  (6, 4, 20), (6, 2, 16), (6, 1, 9),
  (7, 1, 8), (7, 4, 20), (7, 3, 17),  (8, 4, 20), (8, 2, 11), (8, 1, 14),
  (9, 1, 10),  (10, 1, 10),  (11, 1, 10), (12, 1, 10), (13, 1, 10), (14, 1, 10), 
  (15, 1, 10), (16, 1, 10), (17, 1, 10), (18, 1, 10),  (19, 1, 10), (20, 1, 10),
   (21, 1, 10), (22, 1, 10), (23, 1, 10), (24, 1, 10), (25, 1, 10), (26, 1, 10),
  (27, 1, 10), (28, 1, 10), (29, 1, 10), (30, 1, 10), (31, 1, 10), (32, 1, 10),
   (33, 1, 10), (34, 1, 10),  (35, 1, 10), (36, 1, 10), (37, 1, 10), (38, 1, 10),
    (39, 1, 10), (40, 1, 10), (41, 1, 10), (42, 1, 10),  (43, 1, 10), (44, 1, 10), 
    (45, 1, 10), (46, 1, 10), (47, 1, 10), (48, 1, 10), (49, 1, 10), (50, 1, 10),
  (51, 1, 10);



 
INSERT INTO product_attribute (product_id, attribute_option_id) VALUES
  (6000, 1), (6001, 2), (6002, 1), (6003, 3), (6004, 3), (6005, 3), (6006, 2), (6007, 1), (6008, 2), (6009, 1),
  (6010, 2), (6011, 2), (6012, 3), (6013, 1), (6014, 1), (6015, 1), (6016, 2), (6017, 2), (6018, 1), (6019, 2),
  (6020, 1), (6021, 2), (6022, 1), (6023, 1), (6024, 1), (6025, 1), (6026, 1), (6027, 2), (6028, 4), (6029, 4),
  (6030, 4), (6031, 4), (6032, 1), (6033, 2), (6034, 1), (6035, 2), (6036, 2), (6037, 1), (6038, 4), (6039, 2),
  (6040, 2), (6041, 4), (6042, 2), (6043, 4), (6044, 2), (6045, 4), (6046, 4), (6047, NULL), (6048, NULL),
   (6049, NULL), (6050, NULL), (6051, 1);


INSERT INTO product_attribute (product_id, attribute_option_id) VALUES
  (6000, 15), (6001, 17), (6002, 15), (6003, 17), (6004, 19), (6005, 15), (6006, 16), (6007, 19), (6008, 16), (6009, 16),
  (6010, 16), (6011, 18), (6012, 17), (6013, 19), (6014, 18), (6015, 16), (6016, 15), (6017, 16), (6018, 18), (6019, 17),
  (6020, 19), (6021, 19), (6022, 16), (6023, 15), (6024, 16), (6025, 18), (6026, 17), (6027, 19), (6028, 19), (6029, 15),
  (6030, 15), (6031, 16), (6032, 18), (6033, 17), (6034, 19), (6035, 19), (6036, 15), (6037, 15), (6038, 16), (6039, 18),
  (6040, 17), (6041, 19), (6042, 19), (6043, 16), (6044, 15), (6045, 16), (6046, 18), (6047, 17), (6048, 19), (6049, 19), 
  (6050, 15), (6051, 18);


INSERT INTO product_attribute (product_id, attribute_option_id) VALUES
  (6000, 6), (6001, 6), (6002, 6), (6003, 6), (6004, 7), (6005, 6), (6006, 6), (6007, 7), (6008, 7),
  (6009, 6), (6009, 7),  (6010, 6), (6011, 7), (6012, 7), (6013, 6), (6014, 6), (6015, 7), (6016, 7),
   (6017, 6), (6018, 6), (6019, 6),  (6020, 6), (6021, 6), (6022, 7), (6023, 7), (6024, 6), (6025, 6), 
   (6025, 7), (6026, 6), (6027, 7), (6028, 6), (6028, 7),  (6029, 6), (6029, 7), (6030, 7), (6031, 6), 
   (6031, 7), (6032, 6), (6032, 7), (6033, 6), (6033, 7), (6034, 6), (6034, 7),  (6035, 6), (6035, 7), 
   (6036, 6), (6036, 7), (6037, 6), (6037, 7), (6038, 6), (6038, 7), (6039, 6), (6039, 7),  (6040, 6), 
   (6040, 7), (6041, 6), (6041, 7), (6042, 6), (6042, 7), (6043, 6), (6043, 7), (6044, 6), (6044, 7),
  (6045, 6), (6045, 7), (6046, 6), (6046, 7), (6047, 6), (6047, 7), (6048, 6), (6048, 7), (6049, 6),
   (6049, 7),  (6050, 6), (6050, 7), (6051, 6), (6051, 7);


   INSERT INTO product_attribute (product_id, attribute_option_id) VALUES
  (6000, 12), (6001, 12), (6002, 13), (6003, 14), (6004, 14), (6005, 14), (6006, 12), (6007, 14), (6008, 12), (6009, 12),
  (6010, 12), (6011, 12), (6012, 14), (6013, 14), (6014, 13), (6015, 13), (6016, 12), (6017, 14), (6018, 12), (6019, 14),
  (6020, 11), (6021, 12), (6022, 12), (6023, 13), (6024, 12), (6025, 14), (6026, 12), (6027, 12), (6028, 14), (6029, 14),
  (6030, 14), (6031, 12), (6032, 14), (6033, 14), (6034, 14), (6035, 14), (6036, 14), (6037, 12), (6038, 12), (6039, 12),
  (6040, 12), (6041, 12), (6042, 14), (6043, 14), (6044, 14), (6045, 14), (6046, 12), (6047, 14), (6048, 14), (6049, 14),
   (6050, 14), (6051, 13);










INSERT INTO order_status (status_name)
VALUES ('Completed'), ('Pending'),
        ('Canceled');

INSERT INTO user_order (user_order_id, user_id, order_date, payment_method_id, address_id, order_status_id, order_total)
VALUES
(4000, 3000, CURDATE(), 1, 5000, 1, 171.4),
(4001, 3001, CURDATE(), 2, 5001, 1, 175.78);

INSERT INTO order_item (product_item_id, user_order_id, price, quantity) 
VALUES(1, 4000, 41.48, 1),
              (2, 4000, 71.05, 2),
              (3, 4000, 54.87, 1),
              (4, 4001, 35.08, 1),
              (5, 4001, 69.98, 1),
            (6, 4001, 70.72, 1);
        

