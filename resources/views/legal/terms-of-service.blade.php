<!-- Terms of Service Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">
                    <svg class="icon icon-lg me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-description') }}"></use>
                    </svg>
                    Terms of Service
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
                                <strong>Welcome to {{ config('app.name', 'Gawis iHerbal') }}!</strong> These Terms of Service govern your use of our e-wallet and e-commerce platform. Please read them carefully.
                            </div>

                            <h4>1. Acceptance of Terms</h4>
                            <p>By accessing or using {{ config('app.name', 'Gawis iHerbal') }} ("Service"), you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree with any part of these terms, you may not use our Service.</p>

                            <h4>2. Description of Service</h4>
                            <p>{{ config('app.name', 'Gawis iHerbal') }} provides:</p>
                            <ul>
                                <li><strong>E-Wallet Services:</strong> Digital wallet for storing, transferring, and managing funds</li>
                                <li><strong>E-Commerce Platform:</strong> Online marketplace for buying and selling herbal products</li>
                                <li><strong>Payment Processing:</strong> Secure transaction processing for purchases and transfers</li>
                                <li><strong>User Account Management:</strong> Profile and transaction history management</li>
                            </ul>

                            <h4>3. User Accounts</h4>
                            <h5>3.1 Registration</h5>
                            <p>To use our Service, you must create an account by providing accurate, current, and complete information. You are responsible for maintaining the confidentiality of your account credentials.</p>

                            <h5>3.2 Account Security</h5>
                            <p>You agree to:</p>
                            <ul>
                                <li>Keep your password secure and confidential</li>
                                <li>Notify us immediately of any unauthorized use of your account</li>
                                <li>Accept responsibility for all activities under your account</li>
                                <li>Use strong authentication methods when available</li>
                            </ul>

                            <h5>3.3 Account Verification</h5>
                            <p>We may require identity verification before you can use certain features, including but not limited to high-value transactions, withdrawals, or merchant services.</p>

                            <h4>4. E-Wallet Terms</h4>
                            <h5>4.1 Fund Management</h5>
                            <p>Your e-wallet allows you to:</p>
                            <ul>
                                <li>Deposit funds from authorized payment methods</li>
                                <li>Transfer funds to other verified users</li>
                                <li>Make purchases on our e-commerce platform</li>
                                <li>Withdraw funds to linked bank accounts (subject to verification)</li>
                            </ul>

                            <h5>4.2 Transaction Limits</h5>
                            <p>We may impose daily, weekly, or monthly transaction limits for security purposes. These limits may vary based on your account verification level and transaction history.</p>

                            <h5>4.3 Transaction Fees</h5>
                            <p>Certain transactions may be subject to fees, which will be clearly disclosed before you complete the transaction. Current fees include:</p>
                            <ul>
                                <li>Withdrawal fees (varies by method)</li>
                                <li>Currency conversion fees (if applicable)</li>
                                <li>Express transfer fees</li>
                            </ul>

                            <h4>5. E-Commerce Terms</h4>
                            <h5>5.1 Product Listings</h5>
                            <p>All herbal products listed on our platform are subject to:</p>
                            <ul>
                                <li>Quality standards and verification</li>
                                <li>Legal compliance with local regulations</li>
                                <li>Accurate product descriptions and pricing</li>
                                <li>Availability and stock limitations</li>
                            </ul>

                            <h5>5.2 Orders and Payment</h5>
                            <p>When you place an order:</p>
                            <ul>
                                <li>You agree to pay the listed price plus applicable taxes and shipping</li>
                                <li>Payment is processed immediately upon order confirmation</li>
                                <li>Orders are subject to acceptance and inventory availability</li>
                                <li>We reserve the right to cancel orders for any reason</li>
                            </ul>

                            <h5>5.3 Shipping and Delivery</h5>
                            <p>We will make reasonable efforts to deliver products within the estimated timeframe. Delivery times may vary based on location, product availability, and shipping method selected.</p>

                            <h4>6. Prohibited Activities</h4>
                            <p>You agree NOT to:</p>
                            <ul>
                                <li>Use the Service for any illegal or unauthorized purpose</li>
                                <li>Violate any laws in your jurisdiction</li>
                                <li>Transmit viruses, malware, or other harmful code</li>
                                <li>Attempt to gain unauthorized access to our systems</li>
                                <li>Engage in fraudulent activities or money laundering</li>
                                <li>Manipulate prices or engage in market manipulation</li>
                                <li>Use automated systems to access the Service without permission</li>
                                <li>Resell or redistribute herbal products without proper licensing</li>
                            </ul>

                            <h4>7. Privacy and Data Protection</h4>
                            <p>Your privacy is important to us. Our Privacy Policy explains how we collect, use, and protect your information. By using our Service, you consent to our data practices as described in our Privacy Policy.</p>

                            <h4>8. Intellectual Property</h4>
                            <p>The Service and its original content, features, and functionality are owned by {{ config('app.name', 'Gawis iHerbal') }} and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>

                            <h4>9. Disclaimers and Limitation of Liability</h4>
                            <h5>9.1 Health Disclaimers</h5>
                            <p><strong>Important:</strong> Herbal products sold on our platform are not intended to diagnose, treat, cure, or prevent any disease. Consult with a healthcare professional before using any herbal products, especially if you have medical conditions or are taking medications.</p>

                            <h5>9.2 Service Availability</h5>
                            <p>We strive to provide uninterrupted service but cannot guarantee 100% uptime. We reserve the right to suspend or discontinue the Service for maintenance, updates, or other operational reasons.</p>

                            <h5>9.3 Limitation of Liability</h5>
                            <p>To the maximum extent permitted by law, {{ config('app.name', 'Gawis iHerbal') }} shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the Service.</p>

                            <h4>10. Indemnification</h4>
                            <p>You agree to indemnify and hold harmless {{ config('app.name', 'Gawis iHerbal') }}, its officers, directors, employees, and agents from any claims, damages, or expenses arising from your use of the Service or violation of these Terms.</p>

                            <h4>11. Termination</h4>
                            <p>We may terminate or suspend your account and access to the Service immediately, without prior notice, for conduct that we believe violates these Terms or is harmful to other users, us, or third parties.</p>

                            <h4>12. Governing Law</h4>
                            <p>These Terms shall be governed by and construed in accordance with the laws of the jurisdiction where {{ config('app.name', 'Gawis iHerbal') }} operates, without regard to conflict of law provisions.</p>

                            <h4>13. Changes to Terms</h4>
                            <p>We reserve the right to modify these Terms at any time. We will notify users of significant changes via email or platform notification. Continued use of the Service after changes constitutes acceptance of the new Terms.</p>

                            <h4>14. Contact Information</h4>
                            <p>If you have questions about these Terms of Service, please contact us:</p>
                            <div class="card border-0" style="background-color: var(--cui-tertiary-bg); border-color: var(--cui-border-color);">
                                <div class="card-body">
                                    <p class="mb-1"><strong>{{ config('app.name', 'Gawis iHerbal') }}</strong></p>
                                    <p class="mb-1">Email: legal@gawisiherbal.com</p>
                                    <p class="mb-1">Phone: +1 (555) 123-4567</p>
                                    <p class="mb-0">Address: 123 Herbal Street, Wellness City, HC 12345</p>
                                </div>
                            </div>

                            <div class="alert alert-success border-0 mt-4" style="background-color: var(--cui-success-bg-subtle); color: var(--cui-success-text-emphasis); border-color: var(--cui-success-border-subtle);">
                                <strong>Thank you for choosing {{ config('app.name', 'Gawis iHerbal') }}!</strong> We're committed to providing you with a secure and reliable platform for your e-wallet and herbal product needs.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="acceptTerms()">I Accept These Terms</button>
            </div>
        </div>
    </div>
</div>

<script>
function acceptTerms() {
    // Check the terms checkbox if it exists
    const termsCheckbox = document.getElementById('terms');
    if (termsCheckbox) {
        termsCheckbox.checked = true;
    }
    // Close the modal
    const modal = coreui.Modal.getInstance(document.getElementById('termsModal'));
    if (modal) {
        modal.hide();
    }
}
</script>