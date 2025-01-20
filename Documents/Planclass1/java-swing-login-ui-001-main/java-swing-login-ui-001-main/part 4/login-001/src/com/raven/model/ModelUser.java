package com.raven.model;

public class ModelUser {

    public int getUserID() {
        return userID;
    }

    public void setUserID(int userID) {
        this.userID = userID;
    }

    public String getUserName() {
        return userName;
    }

    public void setUserName(String userName) {
        this.userName = userName;
    }
    
    public String getDpi() {
        return dpi;
    }

    public void setDpi(String dpi) {
        this.dpi = dpi;
    }
    
    public String getLastName() {
        return lastName;
    }

    public void setLastName(String lastName) {
        this.lastName = lastName;
    }
    
    public String getPhone() {
        return phone;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }
    
    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getVerifyCode() {
        return verifyCode;
    }

    public void setVerifyCode(String verifyCode) {
        this.verifyCode = verifyCode;
    }

    public ModelUser(int userID, String dpi, String userName, String lastName, String phone, String email, String password, String verifyCode) {
        this.userID = userID;
        this.dpi = dpi;
        this.userName = userName;
        this.lastName = lastName;
        this.phone = phone;
        this.email = email;
        this.password = password;
        this.verifyCode = verifyCode;
    }

    public ModelUser(int userID, String dpi, String userName, String lastName, String phone, String email, String password) {
        this.userID = userID;
        this.dpi = dpi;
        this.userName = userName;
        this.lastName = lastName;
        this.phone = phone;
        this.email = email;
        this.password = password;
    }

    public ModelUser() {
    }

    private int userID;
    private String userName;
    private String lastName;
    private String phone;
    private String dpi;
    private String email;
    private String password;
    private String verifyCode;
}
