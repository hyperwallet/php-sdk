ChangeLog
=========
2.4.4
-------------------
- Added attribute 'isDefaultTransferMethod' to identify default accounts.

2.2.3
-------------------
- Enhanced the code base to support PHP build from version 5.6 to 8.x

2.2.2
-------------------
- Added Support for guzzle 7

2.2.1
-------------------
- Updated helper for formatting document response

2.2.0
-------------------
- Added missing webhook types
- Added taxVerificationStatus to User class
- Added reject reasons to document class
- Updated filters for list endpoints

2.1.1 
-------------------
- Added filter criteria 'limit' to bank accounts

2.1.0 
-------------------
- Added header data to the requests (user-agent, sdk version etc) 
- Added Business Stakeholders status transitions
- Added Transfer status transitions - get, list

2.0.0 
-------------------
- Updated the methods to point to V4 Rest APIs
- Added Business Stakeholders - create, update, list methods
- Added Business Stakeholders - upload multipart document

1.5.1
-------------------
- Added fields processingTime to BankCards, expiresOn to Payments

1.5.0
-------------------
- Added Multipart Upload document 
- Added Transfer refunds
- Added Venmo accounts
- Added User Status Transitions

1.4.0
-------------------
- Fix TypeError thrown when response status is 204 No content
- Add updatePayPalAccount()
- Add CVV field to the sdk
- Remove Relationship field from Server SDK
- Add Business Operating Name Field to User
- Fix incorrect Server address
- Add PayPal account status transitions
- Fix null pointer exception on getMessage

1.3.0 (2019-01-25)
-------------------
- Added field "VerificationStatus" to User
- Client-token endpoint renamed to authentication-token

0.3.1 (2019-01-10)
-------------------

- FIX: Resolved issue with restricted "Accept" & "Content-Type" headers to support only "application/json" or "application/jose+json"

0.3.0 (2018-12-20)
-------------------

- Restricted “Accept” & “Content-Type” headers to support only “application/json” or “application/jose+json”
- Related resources “relatedResources” in error representation is added
- Added Authentication token endpoint

0.2.0 (2018-10-19)
-------------------

- Added PayPal endpoint
- Added transfer endpoint
- Added Layer 7 encryption
- Added bank card endpoint
- Added payment status transition endpoint
- Added get bank account status transition endpoint
- Added list program account receipt endpoint
- Added list user receipt endpoint
- Added list prepaid card receipt endpoint
- Added list program account balance endpoint

0.1.0 (2016-06-30)
------------------

- Initial release
