// Stagin NDI Details
// const authApiUrl = 'https://staging.bhutanndi.com/authentication/authenticate';
// const issuanceApiUrl = 'https://stageclient.bhutanndi.com/issuer/issue-credential';
// const verifierApiUrl = 'https://stageclient.bhutanndi.com/verifier/proof-request';
// const clientId = '3tq7ho23g5risndd90a76jre5f';
// const clientSecret = '111rvn964mucumr6c3qq3n2poilvq5v92bkjh58p121nmoverquh';

const authApiUrl = 'https://core.bhutanndi.com/authentication/authenticate';
const issuanceApiUrl = 'https://app.rsebl.org.bt/issuer/v1/issue-credential';
const verifierApiUrl = 'https://app.rsebl.org.bt/verifier/v1/proof-request';
const clientId = '680nfodbgp7jifbk4qv7nr1phm';
const clientSecret = 'sfqpb68j201ad69cv8rn7ff267p1fn1hf4h63mehne653mnigor';


//PasswordLess Login
function authenticateWithAPI() {
    showLoading();

    const authFormData = new URLSearchParams();
    authFormData.append('client_id', clientId);
    authFormData.append('client_secret', clientSecret);
    authFormData.append('grant_type', 'client_credentials');

    fetch('nats/nats_token.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Access-Control-Allow-Origin': '*'
        },
        body: authFormData,
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! Status: ${response.status}, Message: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        const accessToken = data.access_token;

        const verifierApiData = {
            proofName: 'RSEB Credentials',
            proofAttributes: [
                {
                    'name': "ID Number",
                    'restrictions': [
                        {
                            "schema_name": "https://schema.ngotag.com/schemas/fb675203-b317-4675-a657-be7f5d1d57fb"
                        }
                    ]
                }
            ]
        };

        const verifyFormData = new URLSearchParams();
        verifyFormData.append('access_token', accessToken);
        verifyFormData.append('verifierApiData', JSON.stringify(verifierApiData));

        return fetch('nats/nats_verify.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: verifyFormData,
        });
    })
    .then(verifierResponse => {
        if (!verifierResponse.ok) {
            return verifierResponse.text().then(text => {
                throw new Error(`HTTP error! Status: ${verifierResponse.status}, Message: ${text}`);
            });
        }
        return verifierResponse.json();
    })
    .then(verifierData => {
        console.log('Verifier API Response:', verifierData);

        if (!verifierData.data || !verifierData.data.proofRequestThreadId) {
            throw new Error('Missing proofRequestThreadId in response');
        }

        $('#qrcode').hide();
        const threadId = verifierData.data.proofRequestThreadId;

        const proofRequestURL = verifierData.data.proofRequestURL;
        const deepLinkURL = verifierData.data.deepLinkURL;

        //$("#ndi_div").show();
        const qrCodeOptions = {
            render: 'canvas',
            minVersion: 1,
            maxVersion: 40,
            ecLevel: 'L',
            left: 0,
            top: 0,
            size: 200,
            fill: '#000',
            background: '#fff',
            text: proofRequestURL,
            radius: 0,
            quiet: 0,
            mode: 4,
            mSize: 0.15,
            mPosX: 0.5,
            mPosY: 0.5,
            label: '',
            fontcolor: '#000',
            fontname: 'sans',
            image: document.getElementById('logo'),
        };

        function generateQRCode() {
            $('#qrcode').qrcode(qrCodeOptions);
            $('#qrcode').show();
            $("#ndi_button").hide();
            nats_call(threadId); // Pass accessToken here
        }

        if (window.innerWidth < 992) {
            // For mobile: show QR code, hide the NDI button, update deep link button value
            $('#deepLinkBtn').val(deepLinkURL);
            $('#deepLink').show();
            generateQRCode();
        } else {
            // For desktop/tablet: just show the QR code and hide NDI button
            generateQRCode();
        }

        //startTimer(30);
    })
    .catch(error => {
        console.error('Verifier API Error:', error);
    });
}


$( "#deepLinkBtn" ).on( "click", function() {
    var linkValue = this.value;
    window.location.href = linkValue;
   // deepLinkAuthenticate();
});


//Issuance of Crednetials
function ndiCredentials()
{
   
    showLoading();
    // Prepare data for authentication POST request
    const authFormData = new URLSearchParams();
    authFormData.append('client_id', clientId);
    authFormData.append('client_secret', clientSecret);
    authFormData.append('grant_type', 'client_credentials');

    // Make the authentication API call
    fetch(authApiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: authFormData,
    })
    .then(response => response.json())
    .then(data => {
        // Handle the authentication API response here
        const accessToken = data.access_token;
        // Prepare data for the verifier API request
        const verifierApiData = {
            proofName: 'RSEB Credentials',
            proofAttributes: [
                {
                    name: "ID Number",
                    restrictions: [
                        {
                            schema_id: "https://dev-schema.ngotag.com/schemas/c7952a0a-e9b5-4a4b-a714-1e5d0a1ae076"
                        }
                    ]
                },
                // Add other proofAttributes as needed
            ],
        };
       
        // Make the verifier API call using the obtained access token
        fetch(verifierApiUrl, {
            method: 'POST',  // Use POST for the proof request
            headers: {
                'Authorization': `Bearer ${accessToken}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(verifierApiData),
        })
        .then(verifierResponse => verifierResponse.json())
        .then(verifierData => {
            // Handle the verifier API response here
            const proofRequestURL = verifierData.data.proofRequestURL;
            const threadId = verifierData.data.proofRequestThreadId;
            // Convert the verifier API response to a QR code
            $("#qrcode").qrcode({
                text: proofRequestURL,
                width: 128,
                height: 128,
            });

            
            const relationshipApiUrlWithParams = `${verifierApiUrl}?threadId=${encodeURIComponent(threadId)}`;

            fetch(relationshipApiUrlWithParams, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${accessToken}`,
                    'Content-Type': 'application/json',
                },
            })
            .then(relationshipResponse => relationshipResponse.json())
            .then(relationshipData => {
                

                $.ajax({ 
                    url: 'nats/nats.php?threadId=' + threadId,
                    method: 'GET',
                    success: function(response) {
                        showLoading();
                        var cid_no = response.data.data.requested_presentation.revealed_attrs["ID Number"].value;
                        alert(cid_no);
                        const relationshipid = relationshipData.data.relationshipDid;
                        
                        $.ajax({
                            type: "POST",
                            url: "https://cms.rsebl.org.bt/RSEB/nats/nats_issue_redirect.php",
                            data: { cid_no: cid_no, threadId:threadId, relationshipid:relationshipid},
                            cache: false,
                            dataType: 'JSON',
                            success: function(response) {
                                alert(response);
                                
                                window.location.href = response.url;
                            }
                        });
                    },
                });

            })
            .catch(relationshipError => {
                // Handle errors from the verifier API call
                console.error('Relationship API Error:', relationshipError);
            });
            
        })
        .catch(verifierError => {
            // Handle errors from the verifier API call
            console.error('Verifier API Error:', verifierError);
        });
    })
    .catch(authError => {
        // Handle errors from the authentication API call
        console.error('Authentication API Error:', authError);
    });
}

function handleDropdownChange(selectElement)
{
    var selectedUsername = selectElement.value;
    // ndi_login(response.data.data.requested_presentation.revealed_attrs["ID Number"].value);
    $.ajax({
        type: "POST",
        url: "https://cms.rsebl.org.bt/RSEB/nats/nats_redirect.php",
        data: { selectedUsername: selectedUsername },
        cache: false,
        success: function(response) {
            
            var redirectUrl = response;
            //alert(redirectUrl);
            window.location.href = redirectUrl;
        }
    });
}

function handleSelectChange(selectElement) {
    const threadId = $('#threadId').val();
    const relationshipid = $('#relationshipid').val();
    showLoading();

    const authFormData = new URLSearchParams();
    authFormData.append('client_id', clientId);
    authFormData.append('client_secret', clientSecret);
    authFormData.append('grant_type', 'client_credentials');

    fetch(authApiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: authFormData,
    })
    .then(response => response.json())
    .then(data => {
        const accessToken = data.access_token;

        const issuanceData = {
            credDefId: "9KXYYvCB5vV6ocLDRpgAh5:3:CL:55871:revocable",
            credentialData: {
                Username: selectElement
            },
            forRelationship: relationshipid,
            threadId: threadId
        };

        fetch(issuanceApiUrl, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${accessToken}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json', // Ensure that JSON is expected
            },
            body: JSON.stringify(issuanceData),
        })
        .then(issuanceResponse => {
            alert(issuanceResponse);
            //console.log('Issuance API Response:', issuanceResponse);

            if (!issuanceResponse.ok) {
                throw new Error(`Issuance API Error: ${issuanceResponse.status} - ${issuanceResponse.statusText}`);
            }

            return issuanceResponse.json();
        })
        .then(issuanceData => {
            console.log('Issuance Data:', issuanceData);
            alert(JSON.stringify(issuanceData));
        })
        .catch(issuanceError => {
            console.error('Issuance API Error:', issuanceError);
        });
    })
    .catch(authError => {
        console.error('Authentication API Error:', authError);
    });
}
//Calling The timer function
function startTimer(duration) {
    var timer = duration;
    var intervalId = setInterval(function () {
        var seconds = timer % 60;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        // Set the content of the #timer span
        document.getElementById("timer").textContent = seconds;

        if (--timer < 0) {
            clearInterval(intervalId);
            // Optionally hide the entire counter div if needed
            document.getElementById("clockdiv").style.display = "none";
            location.reload(); // You may want to reconsider reloading the entire page
        }
        document.getElementById("clockdiv").style.display = "block";
    }, 1000);
}

//Calling The main nats function
function nats_call(threadId) {
    var currentHost = window.location.host;
    var operation = "webhook_subscribe";
    
    $.ajax({ 
        url: 'https://' + currentHost + '/RSEB/nats/nats.php',
        data: {
            threadId: threadId,
            webhook_subscribe: operation
        },
        method: 'GET',
        success: function(data) {
        showLoading();
        setTimeout(
            function() {
                $.ajax({ 
                    url: 'https://' + currentHost + '/RSEB/nats/redirect.php',
                    data: {
                        threadId: threadId,
                    },
                    method: 'POST',
                    success: function(response) {
                        window.location.href = response.replace(/\\/g, '');
                    } 
                });
            }, 26000);
        } 
    });
}
