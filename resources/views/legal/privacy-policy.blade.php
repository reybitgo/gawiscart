<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">
                    <svg class="icon icon-lg me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                    </svg>
                    Privacy Policy
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="text-center mb-4">
                        <img class="logo-dark" src="{{ asset('coreui-template/assets/brand/gawis_logo.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                        <img class="logo-light" src="{{ asset('coreui-template/assets/brand/gawis_logo_light.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                        <p class="text-body-secondary mt-2">E-Wallet & E-Commerce Platform</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <p class="text-body-secondary"><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>

                            <div class="alert alert-info border-0" style="background-color: var(--cui-info-bg-subtle); color: var(--cui-info-text-emphasis); border-color: var(--cui-info-border-subtle);">
                                <strong>Your Privacy Matters!</strong> This Privacy Policy explains how {{ config('app.name', 'Gawis iHerbal') }} collects, uses, and protects your personal information.
                            </div>

                            <h4>1. Information We Collect</h4>
                            <h5>1.1 Personal Information</h5>
                            <p>We collect information you provide directly to us, including:</p>
                            <ul>
                                <li><strong>Account Information:</strong> Name, username, email address, phone number</li>
                                <li><strong>Identity Verification:</strong> Government-issued ID, address verification documents</li>
                                <li><strong>Financial Information:</strong> Bank account details, payment card information</li>
                                <li><strong>Profile Information:</strong> Profile pictures, preferences, communication preferences</li>
                            </ul>

                            <h5>1.2 Transaction Information</h5>
                            <p>We collect information about your transactions, including:</p>
                            <ul>
                                <li>Transaction amounts, dates, and times</li>
                                <li>Merchant information and purchase details</li>
                                <li>E-wallet balance and transaction history</li>
                                <li>Shipping and billing addresses</li>
                            </ul>

                            <h5>1.3 Technical Information</h5>
                            <p>We automatically collect certain technical information:</p>
                            <ul>
                                <li><strong>Device Information:</strong> Device type, operating system, browser type</li>
                                <li><strong>Usage Data:</strong> IP address, access times, pages viewed</li>
                                <li><strong>Location Data:</strong> Approximate location based on IP address</li>
                                <li><strong>Cookies and Tracking:</strong> See our Cookie Policy section below</li>
                            </ul>

                            <h4>2. How We Use Your Information</h4>
                            <h5>2.1 Service Provision</h5>
                            <p>We use your information to:</p>
                            <ul>
                                <li>Create and maintain your account</li>
                                <li>Process transactions and payments</li>
                                <li>Provide e-wallet and e-commerce services</li>
                                <li>Send transaction confirmations and account notifications</li>
                            </ul>

                            <h5>2.2 Security and Fraud Prevention</h5>
                            <p>We use your information to:</p>
                            <ul>
                                <li>Verify your identity and prevent fraud</li>
                                <li>Monitor for suspicious activities</li>
                                <li>Comply with anti-money laundering regulations</li>
                                <li>Protect against unauthorized access</li>
                            </ul>

                            <h5>2.3 Communication</h5>
                            <p>We may use your information to:</p>
                            <ul>
                                <li>Send important service announcements</li>
                                <li>Provide customer support</li>
                                <li>Send marketing communications (with your consent)</li>
                                <li>Conduct surveys and gather feedback</li>
                            </ul>

                            <h5>2.4 Legal Compliance</h5>
                            <p>We may use your information to:</p>
                            <ul>
                                <li>Comply with legal obligations</li>
                                <li>Respond to lawful requests from authorities</li>
                                <li>Enforce our Terms of Service</li>
                                <li>Protect our rights and interests</li>
                            </ul>

                            <h4>3. Information Sharing and Disclosure</h4>
                            <h5>3.1 Service Providers</h5>
                            <p>We may share your information with trusted third-party service providers who assist us with:</p>
                            <ul>
                                <li>Payment processing and banking services</li>
                                <li>Identity verification and fraud prevention</li>
                                <li>Cloud hosting and data storage</li>
                                <li>Customer support and communication</li>
                                <li>Marketing and analytics</li>
                            </ul>

                            <h5>3.2 Business Transfers</h5>
                            <p>If we are involved in a merger, acquisition, or sale of assets, your information may be transferred as part of that transaction. We will notify you of any such change in ownership.</p>

                            <h5>3.3 Legal Requirements</h5>
                            <p>We may disclose your information when required by law or to:</p>
                            <ul>
                                <li>Comply with legal processes or government requests</li>
                                <li>Protect our rights, property, or safety</li>
                                <li>Protect the rights, property, or safety of our users</li>
                                <li>Investigate potential violations of our Terms</li>
                            </ul>

                            <h5>3.4 With Your Consent</h5>
                            <p>We may share your information with third parties when you give us explicit consent to do so.</p>

                            <h4>4. Data Security</h4>
                            <h5>4.1 Security Measures</h5>
                            <p>We implement robust security measures to protect your information:</p>
                            <ul>
                                <li><strong>Encryption:</strong> Data is encrypted in transit and at rest</li>
                                <li><strong>Secure Servers:</strong> We use industry-standard secure servers</li>
                                <li><strong>Access Controls:</strong> Limited access to personal information</li>
                                <li><strong>Regular Audits:</strong> Regular security assessments and audits</li>
                                <li><strong>Two-Factor Authentication:</strong> Optional additional security layer</li>
                            </ul>

                            <h5>4.2 Data Retention</h5>
                            <p>We retain your information for as long as necessary to:</p>
                            <ul>
                                <li>Provide our services to you</li>
                                <li>Comply with legal obligations</li>
                                <li>Resolve disputes and enforce agreements</li>
                                <li>Maintain records for regulatory purposes</li>
                            </ul>

                            <h4>5. Your Privacy Rights</h4>
                            <h5>5.1 Access and Correction</h5>
                            <p>You have the right to:</p>
                            <ul>
                                <li>Access your personal information</li>
                                <li>Correct inaccurate information</li>
                                <li>Update your account details</li>
                                <li>Download your data</li>
                            </ul>

                            <h5>5.2 Data Portability</h5>
                            <p>You can request a copy of your data in a structured, commonly used format.</p>

                            <h5>5.3 Deletion Rights</h5>
                            <p>You can request deletion of your personal information, subject to:</p>
                            <ul>
                                <li>Legal retention requirements</li>
                                <li>Ongoing transaction obligations</li>
                                <li>Legitimate business interests</li>
                                <li>Fraud prevention needs</li>
                            </ul>

                            <h5>5.4 Marketing Opt-Out</h5>
                            <p>You can opt-out of marketing communications at any time by:</p>
                            <ul>
                                <li>Clicking unsubscribe links in emails</li>
                                <li>Adjusting your account preferences</li>
                                <li>Contacting our support team</li>
                            </ul>

                            <h4>6. Cookies and Tracking Technologies</h4>
                            <h5>6.1 Types of Cookies</h5>
                            <p>We use several types of cookies:</p>
                            <ul>
                                <li><strong>Essential Cookies:</strong> Required for the service to function</li>
                                <li><strong>Performance Cookies:</strong> Help us analyze site usage</li>
                                <li><strong>Functional Cookies:</strong> Remember your preferences</li>
                                <li><strong>Marketing Cookies:</strong> Used for targeted advertising</li>
                            </ul>

                            <h5>6.2 Cookie Management</h5>
                            <p>You can control cookies through your browser settings. Note that disabling certain cookies may affect site functionality.</p>

                            <h4>7. International Data Transfers</h4>
                            <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data during international transfers.</p>

                            <h4>8. Children's Privacy</h4>
                            <p>Our services are not intended for children under 18. We do not knowingly collect personal information from children under 18. If we discover we have collected such information, we will delete it promptly.</p>

                            <h4>9. California Privacy Rights (CCPA)</h4>
                            <p>If you are a California resident, you have additional rights under the California Consumer Privacy Act:</p>
                            <ul>
                                <li>Right to know what personal information is collected</li>
                                <li>Right to delete personal information</li>
                                <li>Right to opt-out of the sale of personal information</li>
                                <li>Right to non-discrimination for exercising privacy rights</li>
                            </ul>

                            <h4>10. European Privacy Rights (GDPR)</h4>
                            <p>If you are in the European Economic Area, you have rights under the General Data Protection Regulation:</p>
                            <ul>
                                <li>Right of access to your personal data</li>
                                <li>Right to rectification of inaccurate data</li>
                                <li>Right to erasure ("right to be forgotten")</li>
                                <li>Right to restrict processing</li>
                                <li>Right to data portability</li>
                                <li>Right to object to processing</li>
                            </ul>

                            <h4>11. Changes to This Privacy Policy</h4>
                            <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by:</p>
                            <ul>
                                <li>Posting a notice on our platform</li>
                                <li>Sending an email notification</li>
                                <li>Updating the "Last Updated" date</li>
                            </ul>

                            <h4>12. Contact Us</h4>
                            <p>If you have questions about this Privacy Policy or our privacy practices, please contact us:</p>
                            <div class="card border-0" style="background-color: var(--cui-tertiary-bg); border-color: var(--cui-border-color);">
                                <div class="card-body">
                                    <p class="mb-1"><strong>Privacy Officer</strong></p>
                                    <p class="mb-1"><strong>{{ config('app.name', 'Gawis iHerbal') }}</strong></p>
                                    <p class="mb-1">Email: privacy@gawisiherbal.com</p>
                                    <p class="mb-1">Phone: +1 (555) 123-4567</p>
                                    <p class="mb-0">Address: 123 Herbal Street, Wellness City, HC 12345</p>
                                </div>
                            </div>

                            <h4>13. Data Protection Officer</h4>
                            <p>For GDPR-related inquiries, you can contact our Data Protection Officer:</p>
                            <div class="card border-0" style="background-color: var(--cui-tertiary-bg); border-color: var(--cui-border-color);">
                                <div class="card-body">
                                    <p class="mb-1">Email: dpo@gawisiherbal.com</p>
                                    <p class="mb-0">Phone: +1 (555) 123-4568</p>
                                </div>
                            </div>

                            <div class="alert alert-success border-0 mt-4" style="background-color: var(--cui-success-bg-subtle); color: var(--cui-success-text-emphasis); border-color: var(--cui-success-border-subtle);">
                                <strong>Thank you for trusting {{ config('app.name', 'Gawis iHerbal') }} with your information!</strong> We are committed to protecting your privacy and maintaining the security of your personal data.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="acceptPrivacy()">I Understand</button>
            </div>
        </div>
    </div>
</div>

<script>
function acceptPrivacy() {
    // Close the modal
    const modal = coreui.Modal.getInstance(document.getElementById('privacyModal'));
    if (modal) {
        modal.hide();
    }
}
</script>