ChangeLog
=========
1.7.4
-------------------
- Added attribute 'isDefaultTransferMethod' to identify default accounts.

1.7.3
-------------------
- Enhanced the code base to support PHP build from version 5.6 to 8.x

1.7.2
-------------------
- Added Support for guzzle 7

1.7.1
-------------------
- Updated helper for formatting document response

1.7.0
-------------------
- Added missing webhook types
- Added reject reasons to document class
- Added taxVerificationStatus to User class
- Updated filters for list endpoints

1.6.3
-------------------
Updated List webhook filters

1.6.2
-------------------
Updated README file

1.6.1
-------------------
Added custom headers

1.6.0
-------------------
- Added Transfer status transitions - get, list
- Added filters
- Fields added to Transfer Method

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
