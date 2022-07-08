/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (params) {

        const username=$(params.usernameInputSelector);
        const otpBtn=$(params.btnOtpSelector);
        const signInBtn=$(params.signInSelector);
        const password=$(params.passwordInputSelector);
        const boxLogin=$(params.boxLoginSelector);
        const boxAuth=$(params.boxAuthSelector);
        let regmob = new RegExp('^([0|\\+[0-9]{1,5})?([7-9][0-9]{9})$');
        let regEmail=new RegExp('^[\\w-\\.]+@([\\w-]+\\.)+[\\w-]{2,4}$');
        const showSignInBtn = () =>{
            return (regmob.test(username.val()) || regEmail.test(username.val())) && password.val().length>=1?'block':'none';
        }
        const hasCookie = () => {
            return Boolean (document.cookie.indexOf('loginOtp=')>=0)
        }
        const getCookieExpireDate = () => {
            let name = "loginOtp=";
            let decodedCookie = decodeURIComponent(document.cookie);
            let ca = decodedCookie.split(';');
            for(let i = 0; i <ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
        const setCookie = () => {
            if (!hasCookie()){
                const currentDate = new Date();
                document.cookie = "loginOtp="+currentDate+"; expires="+new Date(currentDate.getTime() + params.expireTime*60000)+"; path=/;";
            }
        }
        if (username && otpBtn && signInBtn && password){
            username.on("input",function (value) {
                otpBtn.css("display",()=>{
                    return regmob.test(username.val())?'block':'none'
                });
                signInBtn.css("display",showSignInBtn)
            })
            password.on("input",()=>{
                signInBtn.css("display",showSignInBtn)
            })
            otpBtn.on("click",()=>{
                $.ajax({
                    url: params.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        mobile:username.val()
                    },
                }).success((response)=>{
                    setCookie()
                    boxLogin.css("display","none")
                    boxAuth.css("display","block")
                    const currentDate = new Date(getCookieExpireDate());
                    alert(currentDate.getHours())
                }).error((response)=>{
                   alert(response.responseJSON.messages)
                })
                ;
                return false;
            })
        }

    };
});
