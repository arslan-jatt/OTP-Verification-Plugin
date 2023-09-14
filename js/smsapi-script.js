var fullPhoneNumber; // Declare fullPhoneNumber outside of the event handlers

document.getElementById("send-otp-button").addEventListener("click", () => {
  var sendOtpButton = document.getElementById("send-otp-button");
  sendOtpButton.style.display = "none";
  var phoneNumberInput = document.getElementById("ff_19_num_cellulare");
  var selectedCountryListItem = document.querySelector(
    "li[data-country-code][aria-selected='true']"
  );

  if (selectedCountryListItem) {
    var dialCode = selectedCountryListItem.getAttribute("data-dial-code");
    var number = phoneNumberInput.value.replace(/[\s\-()+]/g, "");

    fullPhoneNumber = dialCode + number;
    // console.log(fullPhoneNumber)
    // Make an AJAX request to send the fullPhoneNumber to the server
    jQuery.post(
      smsapi_params.ajax_url,
      {
        action: "send_sms",
        fullPhoneNumber: fullPhoneNumber,
        security: smsapi_params.nonce,
      },
      function (response) {
        // Handle the response from the server if needed
        console.log(response);
        var verifyOtpButton = document.getElementById("verify-otp-button");
        verifyOtpButton.style.display = "block";
      }
    );
  }
});

var isRequestInProgress = false; // Add a flag to track if a request is in progress

document
  .getElementById("verify-otp-button")
  .addEventListener("click", function () {
    var phoneNumberInput = document.getElementById("ff_19_num_cellulare");
    var selectedCountryListItem = document.querySelector(
      "li[data-country-code][aria-selected='true']"
    );

    if (!isRequestInProgress) {
      isRequestInProgress = true; // Set the flag to indicate a request is in progress
      if (selectedCountryListItem) {
        var userEnteredOTP = document.getElementById(
          "ff_19_codice_conferma"
        ).value;
        var dialCode = selectedCountryListItem.getAttribute("data-dial-code");
        var number = phoneNumberInput.value.replace(/[\s\-()+]/g, "");

        fullPhoneNumber = number;

        // Make an AJAX request to verify the OTP
        jQuery.post(
          smsapi_params.ajax_url,
          {
            action: "verify_otp",
            userEnteredOTP: userEnteredOTP,
            fullPhoneNumber: fullPhoneNumber, // Use fullPhoneNumber here
            security: smsapi_params.nonce,
          },
          function (response) {
            // Handle the response from the server
            // console.log(response);
            var httpCodeMatch = response.match(/HTTP\/\d+\s+(\d+)/);
            if (httpCodeMatch && httpCodeMatch.length > 1) {
              var httpCode = parseInt(httpCodeMatch[1]);

              // Now you have the $httpCode, and you can use it as needed
             if(httpCode === 204){
            var responseContainer = document.getElementById("response-container");
            responseContainer.innerHTML = "Your OTP Is Verified Succesfully.";
            responseContainer.style.color = "Green"; 
            var verifyOtpButton = document.getElementById("verify-otp-button");
            verifyOtpButton.style.display = "none";
             }
              else if(httpCode === 404){
              var responseContainer = document.getElementById("response-container");
              responseContainer.innerHTML = "Your OTP Is Invalid. Please Enter the correct OTP.";
              responseContainer.style.color = "Red"; 
              
               }
               else{
                var responseContainer = document.getElementById("response-container");
                responseContainer.innerHTML = "An error occurred while verifying OTP.";
              responseContainer.style.color = "Red";
               }
            isRequestInProgress = false; // Reset the flag when the request is complete
          }
        }
        );
      }
    }
  });
