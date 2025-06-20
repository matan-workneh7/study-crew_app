<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: -1;
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
            z-index: -1;
        }
        
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.8rem;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        p {
            text-align: center;
            margin-bottom: 40px;
            line-height: 1.6;
            font-weight: 300;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.9;
        }
        
        hr {
            border: none;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.4), transparent);
            margin: 40px auto;
            width: 80%;
        }
        
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            position: relative;
        }
        
        .contact-info {
            flex: 1;
            min-width: 300px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .contact-info h2 {
            margin-bottom: 30px;
            font-size: 1.8rem;
            font-weight: 500;
            position: relative;
            padding-bottom: 10px;
        }
        
        .contact-info h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, #ffffff, rgba(255, 255, 255, 0));
            border-radius: 3px;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            transition: transform 0.3s;
        }
        
        .info-item:hover {
            transform: translateX(5px);
        }
        
        .info-icon {
            font-size: 1.5rem;
            margin-right: 20px;
            color: white;
            min-width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .info-content h3 {
            margin-bottom: 8px;
            font-size: 1.2rem;
            font-weight: 500;
        }
        
        .info-content p {
            text-align: left;
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 300;
            margin-left: 0;
        }
        
        .contact-form {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 20px;
            padding: 40px;
            color: #333;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            transform-style: preserve-3d;
            position: relative;
            overflow: hidden;
        }
        
        .contact-form::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transform: rotate(45deg);
            z-index: 0;
        }
        
        .contact-form > * {
            position: relative;
            z-index: 1;
        }
        
        .contact-form h2 {
            margin-bottom: 25px;
            font-size: 1.8rem;
            color: #667eea;
            font-weight: 500;
            position: relative;
            padding-bottom: 10px;
        }
        
        .contact-form h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, #667eea, rgba(102, 126, 234, 0));
            border-radius: 3px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #555;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(245, 245, 245, 0.7);
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            background: white;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            display: inline-block;
            position: relative;
            overflow: hidden;
        }
        
        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .send-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.3s;
        }
        
        .send-btn:hover::after {
            transform: translateX(0);
        }
        
        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }
            
            .contact-info, .contact-form {
                width: 100%;
            }
            
            h1 {
                font-size: 2.2rem;
            }
            
            body::before, body::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contact Us</h1>
        <p>Welcome to the Study Crew Contact Page! Have questions, feedback, or need support? Reach out via the contact form or email us, and we’ll respond promptly. Thank you for choosing Study Crew!</p>
        
        <hr>
        
        <div class="contact-container">
            <div class="contact-info">
                <h2>Contact Information</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Address</h3>
                        <p>2QGW+8J6, Unnamed Road, Addis Ababa</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Phone</h3>
                        <p>507-</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>Email</h3>
                        <p>info@bitscollege.edu.et</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <h2>Send Message</h2>
                <form>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" value="">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="">
                    </div>
                    <div class="form-group">
                        <label for="message">Type your Message...</label>
                        <textarea id="message"> </textarea>
                    </div>
                    <button type="submit" class="send-btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>