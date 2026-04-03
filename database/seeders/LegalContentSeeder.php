<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IntegrationSetting;

class LegalContentSeeder extends Seeder
{
    public function run()
    {
        // Disclaimer Content (2000+ words)
        $disclaimer = $this->getDisclaimerContent();
        
        // Privacy Policy Content (2000+ words)
        $privacy = $this->getPrivacyContent();
        
        // Terms of Service Content (already 4850 words - keep as is)
        $terms = $this->getTermsContent();
        
        // Save to database
        IntegrationSetting::setSetting('legal', 'disclaimer', $disclaimer);
        IntegrationSetting::setSetting('legal', 'privacy', $privacy);
        IntegrationSetting::setSetting('legal', 'terms', $terms);
        
        $this->command->info('Legal content seeded successfully!');
    }
    
    private function getDisclaimerContent()
    {
        return <<<'HTML'
<h2>Disclaimer</h2>

<h3>1. General Information</h3>
<p>This Monitoring System ("the System") is provided by the Sibu Resident Office, Sarawak State Government, Malaysia. The information contained in this System is for general information purposes only and is intended to assist government agencies, members of parliament, contractors, and other authorized users in managing and monitoring development projects within the Sibu Division.</p>

<p>While we endeavor to keep the information up to date and correct, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability, or availability with respect to the System or the information, products, services, or related graphics contained in the System for any purpose. Any reliance you place on such information is therefore strictly at your own risk.</p>

<p>The Sibu Resident Office reserves the right to make additions, deletions, or modifications to the contents of the System at any time without prior notice. This disclaimer applies to any damages or injury caused by any failure of performance, error, omission, interruption, deletion, defect, delay in operation or transmission, computer virus, communication line failure, theft or destruction, or unauthorized access to, alteration of, or use of record, whether for breach of contract, tortious behavior, negligence, or under any other cause of action.</p>

<h3>2. No Warranty</h3>

<p>The System and all information, content, materials, products, and services included in or otherwise made available to you through the System are provided on an "as is" and "as available" basis, unless otherwise specified in writing. The Sibu Resident Office makes no representations or warranties of any kind, express or implied, as to the operation of the System or the information, content, materials, products, or services included in or otherwise made available to you through the System, unless otherwise specified in writing.</p>

<p>You expressly agree that your use of the System is at your sole risk. To the full extent permissible by applicable law, the Sibu Resident Office disclaims all warranties, express or implied, including, but not limited to, implied warranties of merchantability and fitness for a particular purpose. The Sibu Resident Office does not warrant that the System, its servers, or electronic communications sent from the Sibu Resident Office are free of viruses or other harmful components.</p>

<p>The Sibu Resident Office will not be liable for any damages of any kind arising from the use of the System, including, but not limited to, direct, indirect, incidental, punitive, and consequential damages. This includes damages for loss of profits, use, data, or other intangibles, even if the Sibu Resident Office has been advised of the possibility of such damages.</p>

<h3>3. Accuracy of Information</h3>

<p>The information provided through this System is compiled from various sources including government agencies, parliamentary offices, district offices, and contractor submissions. While every effort is made to ensure that the information is accurate and up-to-date, the Sibu Resident Office does not guarantee the accuracy, completeness, or timeliness of any information displayed in the System.</p>

<p>Project data, budget allocations, implementation timelines, and status updates are subject to change based on various factors including government policy changes, budget revisions, implementation challenges, and other unforeseen circumstances. Users should verify critical information through official channels before making decisions based on data obtained from this System.</p>

<p>The Sibu Resident Office shall not be held responsible for any errors, omissions, or inaccuracies in the information provided, nor for any actions taken in reliance on such information. Users are advised to exercise due diligence and verify information independently when necessary, particularly for matters involving financial commitments, contractual obligations, or policy decisions.</p>

<h3>4. External Links and Third-Party Content</h3>

<p>The System may contain links to external websites or third-party content that are not provided or maintained by the Sibu Resident Office. These links are provided for your convenience and information only. The Sibu Resident Office has no control over the nature, content, and availability of those sites and does not endorse or assume any responsibility for the content, privacy policies, or practices of any third-party websites or services.</p>

<p>The inclusion of any links does not necessarily imply a recommendation or endorse the views expressed within them. Users who access external links do so at their own risk and should be aware that the terms and conditions, including privacy policies, of those external websites may differ from those of this System.</p>

<p>The Sibu Resident Office is not responsible for any loss or damage that may arise from your use of external websites or third-party content. We strongly advise you to read the terms and conditions and privacy policies of any external websites you visit through links provided in this System.</p>

<h3>5. System Availability and Technical Issues</h3>

<p>While we strive to ensure that the System is available 24 hours a day, 7 days a week, the Sibu Resident Office does not guarantee uninterrupted access to the System. The System may be temporarily unavailable due to scheduled maintenance, system upgrades, emergency repairs, or circumstances beyond our control such as power failures, network disruptions, or natural disasters.</p>

<p>The Sibu Resident Office reserves the right to suspend, withdraw, or restrict the availability of all or any part of the System for business and operational reasons without prior notice. We will make reasonable efforts to notify users of planned maintenance activities, but we are not obligated to do so.</p>

<p>Users acknowledge that the System may experience technical difficulties, errors, or interruptions. The Sibu Resident Office shall not be liable for any loss, damage, or inconvenience caused by system downtime, data loss, or technical failures. Users are advised to maintain their own backup copies of important data and not to rely solely on the System for critical information storage.</p>

<h3>6. User Responsibilities and Conduct</h3>

<p>Users of this System are expected to use it responsibly and in accordance with all applicable laws and regulations. Users must not attempt to gain unauthorized access to any part of the System, other user accounts, or computer systems or networks connected to the System through hacking, password mining, or any other means.</p>

<p>Users must not use the System to transmit, distribute, or store material that is unlawful, harmful, threatening, abusive, harassing, defamatory, vulgar, obscene, or otherwise objectionable. Users must not interfere with or disrupt the System or servers or networks connected to the System, or disobey any requirements, procedures, policies, or regulations of networks connected to the System.</p>

<p>The Sibu Resident Office reserves the right to monitor user activity, investigate suspected violations of this disclaimer, and take appropriate action including but not limited to suspending or terminating user access, reporting violations to law enforcement authorities, and pursuing legal action against violators.</p>

<h3>7. Data Accuracy and User Input</h3>

<p>Users who input data into the System are responsible for ensuring the accuracy, completeness, and validity of the information they provide. This includes but is not limited to project details, budget figures, implementation timelines, contractor information, and status updates. Inaccurate or misleading information can have serious consequences for project planning, budget allocation, and decision-making processes.</p>

<p>The Sibu Resident Office is not responsible for errors or omissions in user-submitted data. Users should implement their own verification and quality control procedures to ensure data accuracy before submission. Any discrepancies or errors discovered in the System should be reported immediately to the system administrator for correction.</p>

<p>Users acknowledge that data entered into the System may be used for reporting, analysis, and decision-making purposes by various government agencies and stakeholders. Therefore, users have a responsibility to maintain high standards of data quality and integrity.</p>

<h3>8. Confidentiality and Sensitive Information</h3>

<p>While the System implements security measures to protect user data and system information, users should be aware that no method of electronic transmission or storage is 100% secure. Users should exercise caution when entering sensitive or confidential information into the System.</p>

<p>Users are responsible for maintaining the confidentiality of their login credentials and for all activities that occur under their account. Users should not share their passwords with others and should log out of the System when not in use, especially when accessing the System from shared or public computers.</p>

<p>The Sibu Resident Office shall not be liable for any unauthorized access to user accounts or data breaches that result from user negligence, including but not limited to weak passwords, password sharing, or failure to log out properly.</p>

<h3>9. Intellectual Property Rights</h3>

<p>All content included in or made available through the System, such as text, graphics, logos, images, data compilations, and software, is the property of the Sibu Resident Office, the Sarawak State Government, or its content suppliers and is protected by Malaysian and international copyright laws.</p>

<p>Users are granted a limited, non-exclusive, non-transferable license to access and use the System for its intended purpose. Users may not reproduce, distribute, modify, create derivative works of, publicly display, publicly perform, republish, download, store, or transmit any of the material on the System without prior written consent from the Sibu Resident Office.</p>

<p>Unauthorized use of the System's content may violate copyright, trademark, and other laws and may result in legal action. Users who believe that their intellectual property rights have been violated should contact the Sibu Resident Office immediately.</p>

<h3>10. Limitation of Liability</h3>

<p>To the maximum extent permitted by applicable law, the Sibu Resident Office, its officers, employees, agents, and affiliates shall not be liable for any direct, indirect, incidental, special, consequential, or punitive damages, including but not limited to damages for loss of profits, goodwill, use, data, or other intangible losses, resulting from:</p>

<ul>
<li>Your access to or use of or inability to access or use the System</li>
<li>Any conduct or content of any third party on the System</li>
<li>Any content obtained from the System</li>
<li>Unauthorized access, use, or alteration of your transmissions or content</li>
<li>Errors, mistakes, or inaccuracies in the System's content</li>
<li>Personal injury or property damage resulting from your access to or use of the System</li>
<li>Any interruption or cessation of transmission to or from the System</li>
<li>Any bugs, viruses, trojan horses, or the like that may be transmitted to or through the System</li>
<li>Any errors or omissions in any content or for any loss or damage incurred as a result of the use of any content posted, emailed, transmitted, or otherwise made available through the System</li>
</ul>

<p>This limitation of liability applies whether the alleged liability is based on contract, tort, negligence, strict liability, or any other basis, even if the Sibu Resident Office has been advised of the possibility of such damage.</p>

<h3>11. Indemnification</h3>

<p>Users agree to indemnify, defend, and hold harmless the Sibu Resident Office, the Sarawak State Government, and their respective officers, employees, agents, and affiliates from and against any and all claims, damages, obligations, losses, liabilities, costs, and expenses (including but not limited to attorney's fees) arising from:</p>

<ul>
<li>Your use of and access to the System</li>
<li>Your violation of any term of this disclaimer</li>
<li>Your violation of any third-party right, including without limitation any copyright, property, or privacy right</li>
<li>Any claim that your use of the System caused damage to a third party</li>
</ul>

<p>This indemnification obligation will survive the termination of your use of the System and your relationship with the Sibu Resident Office.</p>

<h3>12. Modifications to Disclaimer</h3>

<p>The Sibu Resident Office reserves the right to modify, amend, or update this disclaimer at any time without prior notice. Changes will be effective immediately upon posting to the System. Your continued use of the System after any such changes constitutes your acceptance of the new disclaimer.</p>

<p>It is your responsibility to review this disclaimer periodically for changes. If you do not agree with any modifications to this disclaimer, you must discontinue use of the System immediately.</p>

<h3>13. Governing Law and Jurisdiction</h3>

<p>This disclaimer and your use of the System shall be governed by and construed in accordance with the laws of Malaysia. Any disputes arising out of or relating to this disclaimer or the use of the System shall be subject to the exclusive jurisdiction of the courts of Malaysia.</p>

<p>If any provision of this disclaimer is found to be invalid or unenforceable by a court of competent jurisdiction, the remaining provisions shall continue in full force and effect. The failure of the Sibu Resident Office to enforce any right or provision of this disclaimer shall not constitute a waiver of such right or provision.</p>

<h3>14. Contact Information</h3>

<p>If you have any questions, concerns, or complaints regarding this disclaimer or the System, please contact us at:</p>

<p><strong>Sibu Resident Office</strong><br>
Aras 5, Kompleks Islam Sarawak Sibu<br>
Jalan Awang Ramli Amit<br>
96000 Sibu, Sarawak<br>
Malaysia</p>

<p><strong>Tel:</strong> 084-330202 / 082-318963 / 082-321963<br>
<strong>Fax:</strong> 084-320970 / 082-347701 / 082-317214<br>
<strong>Website:</strong> <a href="https://sibu.sarawak.gov.my" target="_blank">https://sibu.sarawak.gov.my</a><br>
<strong>Email:</strong> <a href="mailto:khairuni90@sarawak.gov.my">khairuni90@sarawak.gov.my</a></p>

<p><em>Last Updated: February 2026</em></p>
HTML;
    }
    
    private function getPrivacyContent()
    {
        return <<<'HTML'
<h2>Privacy Policy</h2>

<h3>1. Introduction and Scope</h3>
<p>The Sibu Resident Office ("we," "us," or "our") is committed to protecting the privacy and security of personal information collected through the Monitoring System ("the System"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our System. This policy applies to all users including government agency personnel, members of parliament, contractors, and other authorized users.</p>

<p>By accessing or using the System, you acknowledge that you have read, understood, and agree to be bound by this Privacy Policy. If you do not agree with the terms of this Privacy Policy, please do not access or use the System. We reserve the right to make changes to this Privacy Policy at any time and for any reason. We will alert you about any changes by updating the "Last Updated" date of this Privacy Policy.</p>

<p>This Privacy Policy is designed to comply with the Personal Data Protection Act 2010 (PDPA) of Malaysia and other applicable data protection laws and regulations. We are committed to handling your personal information in accordance with these legal requirements and best practices in data protection.</p>

<h3>2. Information We Collect</h3>

<p><strong>2.1 Personal Information</strong></p>

<p>We collect personal information that you voluntarily provide to us when you register for an account, use the System, or contact us. The personal information we collect may include:</p>

<ul>
<li><strong>Account Information:</strong> Full name, username, email address, phone number, job title, department, organization/agency affiliation, and password</li>
<li><strong>Profile Information:</strong> Professional details, role within your organization, areas of responsibility, and user preferences</li>
<li><strong>Contact Information:</strong> Mailing address, office location, and alternative contact details</li>
<li><strong>Identification Information:</strong> Government employee ID, MyKad number (for verification purposes), and other official identification as required</li>
<li><strong>Professional Credentials:</strong> Qualifications, certifications, and authorization levels relevant to your role</li>
</ul>

<p><strong>2.2 Project and Operational Data</strong></p>

<p>When you use the System to manage projects and operations, we collect information including:</p>

<ul>
<li>Project details, descriptions, and specifications</li>
<li>Budget allocations, expenditures, and financial data</li>
<li>Implementation timelines, milestones, and progress reports</li>
<li>Contractor information and performance data</li>
<li>Approval workflows and decision records</li>
<li>Comments, notes, and communications related to projects</li>
<li>Document uploads and attachments</li>
<li>Status updates and change requests</li>
</ul>

<p><strong>2.3 Technical and Usage Information</strong></p>

<p>We automatically collect certain information when you access and use the System, including:</p>

<ul>
<li><strong>Log Data:</strong> IP address, browser type and version, operating system, access times, pages viewed, and the page you visited before navigating to our System</li>
<li><strong>Device Information:</strong> Hardware model, device identifiers, and mobile network information</li>
<li><strong>Usage Data:</strong> Features used, actions taken, time spent on pages, and interaction patterns</li>
<li><strong>Location Data:</strong> General geographic location based on IP address</li>
<li><strong>Cookies and Similar Technologies:</strong> Information collected through cookies, web beacons, and similar tracking technologies</li>
</ul>

<h3>3. How We Use Your Information</h3>

<p>We use the information we collect for various purposes, including:</p>

<p><strong>3.1 System Operation and Management</strong></p>

<ul>
<li>To create and manage user accounts and authenticate users</li>
<li>To provide, operate, and maintain the System's functionality</li>
<li>To process and manage project data, budget allocations, and approvals</li>
<li>To facilitate communication and collaboration among authorized users</li>
<li>To generate reports, analytics, and insights for decision-making</li>
<li>To track project progress and monitor implementation</li>
</ul>

<p><strong>3.2 System Improvement and Development</strong></p>

<ul>
<li>To understand how users interact with the System and identify areas for improvement</li>
<li>To develop new features, functionality, and services</li>
<li>To conduct research and analysis to enhance user experience</li>
<li>To test and troubleshoot technical issues</li>
<li>To optimize System performance and reliability</li>
</ul>

<p><strong>3.3 Security and Compliance</strong></p>

<ul>
<li>To detect, prevent, and address technical issues, fraud, and security threats</li>
<li>To enforce our terms of service and policies</li>
<li>To comply with legal obligations and regulatory requirements</li>
<li>To protect the rights, property, and safety of the Sibu Resident Office, users, and the public</li>
<li>To maintain audit trails and accountability records</li>
</ul>

<p><strong>3.4 Communication and Support</strong></p>

<ul>
<li>To respond to your inquiries, requests, and support needs</li>
<li>To send administrative information, updates, and notifications</li>
<li>To provide training and technical assistance</li>
<li>To communicate important changes to the System or policies</li>
</ul>

<h3>4. Information Sharing and Disclosure</h3>

<p>We may share your information in the following circumstances:</p>

<p><strong>4.1 Within Government Agencies</strong></p>

<p>Information may be shared with other government agencies, departments, and offices within the Sarawak State Government and Malaysian Federal Government for official purposes including:</p>

<ul>
<li>Project coordination and implementation</li>
<li>Budget planning and allocation</li>
<li>Policy development and decision-making</li>
<li>Performance monitoring and evaluation</li>
<li>Compliance with government directives and regulations</li>
</ul>

<p><strong>4.2 With Service Providers</strong></p>

<p>We may share information with third-party service providers who perform services on our behalf, such as:</p>

<ul>
<li>IT infrastructure and hosting providers</li>
<li>System maintenance and support services</li>
<li>Data backup and disaster recovery services</li>
<li>Security and fraud prevention services</li>
<li>Analytics and performance monitoring services</li>
</ul>

<p>These service providers are contractually obligated to protect your information and use it only for the purposes for which it was disclosed.</p>

<p><strong>4.3 Legal Requirements</strong></p>

<p>We may disclose your information if required to do so by law or in response to valid requests by public authorities, including:</p>

<ul>
<li>Compliance with legal processes such as court orders or subpoenas</li>
<li>Enforcement of our terms and conditions</li>
<li>Protection of our rights, privacy, safety, or property</li>
<li>Investigation of potential violations or illegal activities</li>
<li>Response to government or regulatory inquiries</li>
</ul>

<p><strong>4.4 Business Transfers</strong></p>

<p>In the event of a reorganization, merger, or transfer of the System to another government entity, your information may be transferred as part of that transaction. We will notify you of any such change and the choices you may have regarding your information.</p>

<h3>5. Data Security</h3>

<p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>

<p><strong>5.1 Technical Safeguards</strong></p>

<ul>
<li>Encryption of data in transit and at rest using industry-standard protocols</li>
<li>Secure authentication mechanisms including password protection and multi-factor authentication</li>
<li>Regular security assessments and vulnerability testing</li>
<li>Intrusion detection and prevention systems</li>
<li>Secure backup and disaster recovery procedures</li>
<li>Network security controls including firewalls and access controls</li>
</ul>

<p><strong>5.2 Organizational Safeguards</strong></p>

<ul>
<li>Access controls limiting information access to authorized personnel only</li>
<li>Regular security training for staff and users</li>
<li>Confidentiality agreements with employees and service providers</li>
<li>Incident response and breach notification procedures</li>
<li>Regular audits and compliance reviews</li>
<li>Data minimization and retention policies</li>
</ul>

<p>However, please be aware that no method of transmission over the internet or electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your personal information, we cannot guarantee its absolute security. Users also have a responsibility to protect their account credentials and report any suspected security breaches immediately.</p>

<h3>6. Data Retention</h3>

<p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. The retention period depends on various factors including:</p>

<ul>
<li>The nature and sensitivity of the information</li>
<li>The purposes for which we process the information</li>
<li>Legal, regulatory, and contractual obligations</li>
<li>Operational and business requirements</li>
<li>Audit and accountability needs</li>
</ul>

<p>When personal information is no longer needed, we will securely delete or anonymize it in accordance with our data retention and disposal policies. Project data and operational records may be retained for longer periods as required by government record-keeping requirements and archival policies.</p>

<h3>7. Your Rights and Choices</h3>

<p>Under the Personal Data Protection Act 2010 and other applicable laws, you have certain rights regarding your personal information:</p>

<p><strong>7.1 Access and Correction</strong></p>

<p>You have the right to access your personal information held by us and to request correction of any inaccurate or incomplete information. You can update your profile information directly through the System or contact us for assistance.</p>

<p><strong>7.2 Data Portability</strong></p>

<p>You may request a copy of your personal information in a structured, commonly used, and machine-readable format, subject to technical feasibility and legal limitations.</p>

<p><strong>7.3 Withdrawal of Consent</strong></p>

<p>Where we process your personal information based on your consent, you have the right to withdraw that consent at any time. However, this will not affect the lawfulness of processing based on consent before its withdrawal.</p>

<p><strong>7.4 Objection and Restriction</strong></p>

<p>You may object to certain types of processing of your personal information or request restriction of processing in certain circumstances, subject to legal and operational requirements.</p>

<p><strong>7.5 Complaints</strong></p>

<p>If you believe your privacy rights have been violated or you have concerns about how we handle your personal information, you have the right to lodge a complaint with us or with the Personal Data Protection Commissioner of Malaysia.</p>

<h3>8. Cookies and Tracking Technologies</h3>

<p>The System uses cookies and similar tracking technologies to enhance user experience and collect usage information. Cookies are small data files stored on your device that help us:</p>

<ul>
<li>Remember your login credentials and preferences</li>
<li>Maintain your session and keep you logged in</li>
<li>Understand how you use the System</li>
<li>Improve System performance and functionality</li>
<li>Provide personalized features and content</li>
</ul>

<p>You can control cookie settings through your browser preferences. However, disabling cookies may affect your ability to use certain features of the System. We use the following types of cookies:</p>

<ul>
<li><strong>Essential Cookies:</strong> Required for the System to function properly</li>
<li><strong>Functional Cookies:</strong> Enable enhanced functionality and personalization</li>
<li><strong>Analytics Cookies:</strong> Help us understand System usage and performance</li>
<li><strong>Security Cookies:</strong> Support security features and fraud prevention</li>
</ul>

<h3>9. Children's Privacy</h3>

<p>The System is not intended for use by individuals under the age of 18. We do not knowingly collect personal information from children. If we become aware that we have collected personal information from a child without parental consent, we will take steps to delete that information promptly.</p>

<h3>10. International Data Transfers</h3>

<p>Your information is primarily stored and processed within Malaysia. However, some of our service providers may be located in other countries. When we transfer personal information internationally, we ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy and applicable data protection laws.</p>

<h3>11. Third-Party Services and Integrations</h3>

<p>The System may integrate with or link to third-party services, applications, or websites to enhance functionality and user experience. These third-party services may include:</p>

<ul>
<li>Cloud storage and file sharing services</li>
<li>Email and communication platforms</li>
<li>SMS notification services</li>
<li>Weather information services</li>
<li>Mapping and geolocation services</li>
<li>Analytics and monitoring tools</li>
<li>Authentication and identity verification services</li>
</ul>

<p>When you use these third-party services through the System, your information may be collected, used, and shared by those third parties in accordance with their own privacy policies. We are not responsible for the privacy practices of third-party services and encourage you to review their privacy policies before using them.</p>

<p>We carefully select third-party service providers and require them to maintain appropriate security measures and comply with applicable data protection laws. However, we cannot guarantee the security or privacy of information processed by third parties.</p>

<h3>12. Automated Decision-Making and Profiling</h3>

<p>The System may use automated processes to analyze data and generate insights, reports, and recommendations. These automated processes may include:</p>

<ul>
<li>Budget allocation analysis and recommendations</li>
<li>Project performance monitoring and alerts</li>
<li>Risk assessment and identification</li>
<li>Resource optimization suggestions</li>
<li>Trend analysis and forecasting</li>
</ul>

<p>While these automated processes assist in decision-making, final decisions regarding projects, budgets, and approvals are made by authorized human users. You have the right to request human review of any automated decision that significantly affects you.</p>

<h3>13. Data Breach Notification</h3>

<p>In the event of a data breach that compromises the security of your personal information, we will:</p>

<ul>
<li>Investigate the breach promptly and thoroughly</li>
<li>Take immediate steps to contain and mitigate the breach</li>
<li>Notify affected users within 72 hours of becoming aware of the breach, where feasible</li>
<li>Provide information about the nature of the breach, the data affected, and steps being taken</li>
<li>Offer guidance on protective measures users can take</li>
<li>Report the breach to relevant authorities as required by law</li>
<li>Implement additional security measures to prevent future breaches</li>
</ul>

<p>We maintain an incident response plan and conduct regular security drills to ensure we can respond effectively to data breaches and security incidents.</p>

<h3>14. Cross-Border Data Transfers</h3>

<p>While your personal information is primarily stored and processed within Malaysia, certain circumstances may require international data transfers, such as:</p>

<ul>
<li>Use of cloud services with servers in multiple countries</li>
<li>Technical support from international service providers</li>
<li>Collaboration with international development partners</li>
<li>Compliance with international reporting requirements</li>
</ul>

<p>When we transfer personal information internationally, we ensure that:</p>

<ul>
<li>Adequate safeguards are in place to protect your information</li>
<li>Transfers comply with Malaysian data protection laws</li>
<li>Receiving parties maintain appropriate security standards</li>
<li>Contractual protections are established where necessary</li>
<li>You are informed of such transfers where required by law</li>
</ul>

<h3>15. Special Categories of Personal Data</h3>

<p>We generally do not collect or process special categories of personal data (also known as sensitive personal data) such as information about race, ethnicity, religious beliefs, health, or biometric data. However, in limited circumstances, such information may be collected if:</p>

<ul>
<li>Required by law or regulation</li>
<li>Necessary for the performance of official government functions</li>
<li>You have provided explicit consent</li>
<li>Necessary for legal claims or proceedings</li>
</ul>

<p>When we process special categories of personal data, we implement additional security measures and access controls to protect this sensitive information.</p>

<h3>16. Accountability and Governance</h3>

<p>We are committed to accountability and transparency in our data protection practices. Our accountability measures include:</p>

<ul>
<li>Designation of a data protection officer or privacy coordinator</li>
<li>Regular privacy impact assessments for new systems and processes</li>
<li>Documentation of data processing activities and purposes</li>
<li>Staff training on data protection and privacy requirements</li>
<li>Regular audits and compliance reviews</li>
<li>Maintenance of records of processing activities</li>
<li>Implementation of privacy by design and default principles</li>
</ul>

<p>We maintain comprehensive documentation of our data protection practices and make this information available to regulatory authorities upon request.</p>

<h3>17. Changes to This Privacy Policy</h3>

<p>We may update this Privacy Policy from time to time to reflect changes in our practices, technology, legal requirements, or other factors. We will notify you of any material changes by posting the new Privacy Policy on the System and updating the "Last Updated" date. Your continued use of the System after such changes constitutes your acceptance of the updated Privacy Policy.</p>

<p>We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information. If you have questions or concerns about changes to this Privacy Policy, please contact us.</p>

<p>For significant changes that materially affect your rights or how we process your personal information, we may provide additional notice through email or prominent notices within the System. We will also maintain an archive of previous versions of this Privacy Policy for your reference.</p>

<h3>18. Contact Information</h3>

<p>If you have any questions, concerns, or requests regarding this Privacy Policy or our privacy practices, please contact us at:</p>

<p><strong>Sibu Resident Office</strong><br>
Aras 5, Kompleks Islam Sarawak Sibu<br>
Jalan Awang Ramli Amit<br>
96000 Sibu, Sarawak<br>
Malaysia</p>

<p><strong>Tel:</strong> 084-330202 / 082-318963 / 082-321963<br>
<strong>Fax:</strong> 084-320970 / 082-347701 / 082-317214<br>
<strong>Website:</strong> <a href="https://sibu.sarawak.gov.my" target="_blank">https://sibu.sarawak.gov.my</a><br>
<strong>Email:</strong> <a href="mailto:khairuni90@sarawak.gov.my">khairuni90@sarawak.gov.my</a></p>

<p><em>Last Updated: February 2026</em></p>
HTML;
    }
    
    private function getTermsContent()
    {
        return <<<'HTML'
<h2>Terms of Service</h2>

<h3>1. Acceptance of Terms</h3>
<p>Welcome to the Monitoring System operated by the Sibu Resident Office, Sarawak State Government. These Terms of Service ("Terms") govern your access to and use of the Monitoring System ("System"), including any content, functionality, and services offered on or through the System. By accessing or using the System, you agree to be bound by these Terms and our Privacy Policy. If you do not agree to these Terms, you must not access or use the System.</p>

<p>These Terms constitute a legally binding agreement between you ("User," "you," or "your") and the Sibu Resident Office ("we," "us," or "our"). We reserve the right to modify these Terms at any time. We will notify users of any material changes by posting the updated Terms on the System. Your continued use of the System after such modifications constitutes your acceptance of the updated Terms.</p>

<p>The System is designed to facilitate the management, monitoring, and coordination of development projects within the Sibu Division. Access to the System is restricted to authorized users including government agency personnel, members of parliament, contractors, and other stakeholders involved in project implementation and oversight.</p>

<h3>2. User Eligibility and Account Registration</h3>

<p><strong>2.1 Eligibility Requirements</strong></p>

<p>To use the System, you must:</p>

<ul>
<li>Be at least 18 years of age</li>
<li>Be an authorized representative of a government agency, parliamentary office, contractor organization, or other entity approved by the Sibu Resident Office</li>
<li>Have the legal authority to enter into these Terms on behalf of yourself and, if applicable, your organization</li>
<li>Provide accurate, current, and complete information during the registration process</li>
<li>Maintain and promptly update your account information to keep it accurate and current</li>
</ul>

<p><strong>2.2 Account Creation and Security</strong></p>

<p>When you create an account, you agree to:</p>

<ul>
<li>Provide truthful, accurate, and complete registration information</li>
<li>Maintain the security and confidentiality of your account credentials</li>
<li>Notify us immediately of any unauthorized access to or use of your account</li>
<li>Accept responsibility for all activities that occur under your account</li>
<li>Not share your account credentials with any third party</li>
<li>Use a strong, unique password and change it regularly</li>
<li>Log out of your account when not in use, especially on shared or public computers</li>
</ul>

<p>We reserve the right to suspend or terminate accounts that violate these Terms or pose a security risk to the System. You are solely responsible for any and all activities conducted through your account, whether or not you authorized such activities.</p>

<p><strong>2.3 Account Types and Access Levels</strong></p>

<p>The System provides different account types with varying access levels based on user roles:</p>

<ul>
<li><strong>Residen (Administrator):</strong> Full system access including system settings, user management, and all project data</li>
<li><strong>Agency Users:</strong> Access to projects and data related to their specific government agency</li>
<li><strong>Parliament/DUN Users:</strong> Access to projects and data within their parliamentary or state assembly constituency</li>
<li><strong>Contractor Users:</strong> Limited access to view projects assigned to their contractor organization</li>
</ul>

<p>Access levels are assigned based on your organizational affiliation and role. You agree to use the System only within the scope of your authorized access level and not to attempt to access information or functionality beyond your authorization.</p>

<h3>3. Acceptable Use Policy</h3>

<p><strong>3.1 Permitted Uses</strong></p>

<p>You may use the System only for lawful purposes and in accordance with these Terms. Specifically, you agree to use the System to:</p>

<ul>
<li>Manage and monitor development projects within your area of responsibility</li>
<li>Input, update, and maintain accurate project information</li>
<li>Track budget allocations, expenditures, and project progress</li>
<li>Facilitate communication and coordination among project stakeholders</li>
<li>Generate reports and analytics for decision-making and oversight</li>
<li>Submit and process project approvals and change requests</li>
<li>Maintain records and documentation related to project implementation</li>
</ul>

<p><strong>3.2 Prohibited Uses</strong></p>

<p>You agree not to use the System to:</p>

<ul>
<li>Violate any applicable local, state, national, or international law or regulation</li>
<li>Infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
<li>Transmit, or procure the sending of, any advertising or promotional material without our prior written consent</li>
<li>Impersonate or attempt to impersonate the Sibu Resident Office, another user, or any other person or entity</li>
<li>Engage in any conduct that restricts or inhibits anyone's use or enjoyment of the System</li>
<li>Use the System in any manner that could disable, overburden, damage, or impair the System</li>
<li>Use any robot, spider, or other automatic device to access the System for any purpose without our express written permission</li>
<li>Introduce any viruses, trojan horses, worms, logic bombs, or other material that is malicious or technologically harmful</li>
<li>Attempt to gain unauthorized access to any portion of the System, other user accounts, or any systems or networks connected to the System</li>
<li>Attack the System via a denial-of-service attack or a distributed denial-of-service attack</li>
<li>Otherwise attempt to interfere with the proper working of the System</li>
<li>Use the System to collect, harvest, or compile information about other users without their consent</li>
<li>Use the System for any commercial purpose not expressly authorized by these Terms</li>
<li>Modify, adapt, translate, reverse engineer, decompile, or disassemble any portion of the System</li>
<li>Remove, obscure, or alter any copyright, trademark, or other proprietary rights notices</li>
</ul>

<h3>4. Data Input and Accuracy</h3>

<p><strong>4.1 User Responsibilities</strong></p>

<p>Users who input data into the System are responsible for:</p>

<ul>
<li>Ensuring the accuracy, completeness, and validity of all information provided</li>
<li>Verifying data before submission, particularly financial figures and project details</li>
<li>Updating information promptly when changes occur</li>
<li>Maintaining proper documentation to support data entered into the System</li>
<li>Correcting errors or inaccuracies as soon as they are discovered</li>
<li>Following established data entry standards and procedures</li>
<li>Obtaining necessary approvals before entering or modifying sensitive information</li>
</ul>

<p><strong>4.2 Data Quality Standards</strong></p>

<p>All data entered into the System must meet the following quality standards:</p>

<ul>
<li>Accuracy: Information must be correct and free from errors</li>
<li>Completeness: All required fields must be filled with appropriate information</li>
<li>Consistency: Data must be consistent across related records and fields</li>
<li>Timeliness: Information must be current and updated regularly</li>
<li>Validity: Data must conform to specified formats, ranges, and business rules</li>
<li>Integrity: Information must be maintained in a way that ensures its reliability and trustworthiness</li>
</ul>

<p>Failure to maintain data quality standards may result in account suspension or termination. Intentional submission of false or misleading information may result in legal action and criminal prosecution under applicable laws.</p>

<h3>5. Intellectual Property Rights</h3>

<p><strong>5.1 Ownership</strong></p>

<p>The System and its entire contents, features, and functionality (including but not limited to all information, software, text, displays, images, video, and audio, and the design, selection, and arrangement thereof) are owned by the Sibu Resident Office, the Sarawak State Government, or its licensors and are protected by Malaysian and international copyright, trademark, patent, trade secret, and other intellectual property or proprietary rights laws.</p>

<p><strong>5.2 Limited License</strong></p>

<p>Subject to your compliance with these Terms, we grant you a limited, non-exclusive, non-transferable, non-sublicensable, revocable license to access and use the System for its intended purpose. This license does not include any right to:</p>

<ul>
<li>Resell or make commercial use of the System or its contents</li>
<li>Collect and use any product listings, descriptions, or prices</li>
<li>Make derivative use of the System or its contents</li>
<li>Download or copy account information for the benefit of another party</li>
<li>Use any data mining, robots, or similar data gathering and extraction tools</li>
</ul>

<p><strong>5.3 User Content</strong></p>

<p>By submitting content to the System (including project data, documents, comments, and other materials), you grant us a worldwide, non-exclusive, royalty-free, perpetual, irrevocable license to use, reproduce, modify, adapt, publish, translate, distribute, and display such content for the purposes of operating and improving the System and fulfilling our governmental functions. You represent and warrant that you have all necessary rights to grant this license and that your content does not violate any third-party rights.</p>

<h3>6. Privacy and Data Protection</h3>

<p>Your use of the System is also governed by our Privacy Policy, which is incorporated into these Terms by reference. By using the System, you consent to the collection, use, and disclosure of your information as described in the Privacy Policy. We are committed to protecting your personal information in accordance with the Personal Data Protection Act 2010 (PDPA) and other applicable data protection laws.</p>

<p>You acknowledge that:</p>

<ul>
<li>Information you provide may be shared with other government agencies for official purposes</li>
<li>Project data may be used for reporting, analysis, and policy-making</li>
<li>We implement security measures to protect your information, but no system is completely secure</li>
<li>You are responsible for maintaining the confidentiality of your account credentials</li>
<li>You should not include sensitive personal information in project descriptions or comments unless necessary</li>
</ul>

<h3>7. System Availability and Modifications</h3>

<p><strong>7.1 Availability</strong></p>

<p>While we strive to provide continuous access to the System, we do not guarantee that the System will be available at all times or that access will be uninterrupted or error-free. The System may be unavailable due to:</p>

<ul>
<li>Scheduled maintenance and upgrades</li>
<li>Emergency repairs and security updates</li>
<li>Technical difficulties or system failures</li>
<li>Circumstances beyond our control (force majeure events)</li>
<li>Security incidents or threats</li>
</ul>

<p>We will make reasonable efforts to notify users of planned maintenance, but we are not obligated to do so. We shall not be liable for any loss or damage resulting from system downtime or unavailability.</p>

<p><strong>7.2 Modifications to the System</strong></p>

<p>We reserve the right to modify, suspend, or discontinue any aspect of the System at any time, with or without notice. This includes the right to:</p>

<ul>
<li>Add, modify, or remove features and functionality</li>
<li>Change the user interface and user experience</li>
<li>Update system requirements and technical specifications</li>
<li>Implement new security measures or access controls</li>
<li>Modify data structures and reporting formats</li>
</ul>

<p>We will make reasonable efforts to minimize disruption to users when making significant changes to the System. However, we shall not be liable for any consequences resulting from modifications to the System.</p>

<h3>8. Disclaimer of Warranties</h3>

<p>THE SYSTEM IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED. TO THE FULLEST EXTENT PERMISSIBLE UNDER APPLICABLE LAW, WE DISCLAIM ALL WARRANTIES, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO:</p>

<ul>
<li>IMPLIED WARRANTIES OF MERCHANTABILITY</li>
<li>IMPLIED WARRANTIES OF FITNESS FOR A PARTICULAR PURPOSE</li>
<li>IMPLIED WARRANTIES OF NON-INFRINGEMENT</li>
<li>WARRANTIES ARISING FROM COURSE OF DEALING OR COURSE OF PERFORMANCE</li>
</ul>

<p>WE DO NOT WARRANT THAT:</p>

<ul>
<li>The System will meet your requirements or expectations</li>
<li>The System will be available, uninterrupted, secure, or error-free</li>
<li>The results obtained from using the System will be accurate or reliable</li>
<li>The quality of any products, services, information, or other material obtained through the System will meet your expectations</li>
<li>Any errors in the System will be corrected</li>
</ul>

<p>No advice or information, whether oral or written, obtained by you from us or through the System shall create any warranty not expressly stated in these Terms.</p>

<h3>9. Limitation of Liability</h3>

<p>TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT SHALL THE SIBU RESIDENT OFFICE, THE SARAWAK STATE GOVERNMENT, OR THEIR RESPECTIVE OFFICERS, EMPLOYEES, AGENTS, OR AFFILIATES BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO:</p>

<ul>
<li>Loss of profits, revenue, or business opportunities</li>
<li>Loss of data or information</li>
<li>Loss of goodwill or reputation</li>
<li>Business interruption</li>
<li>Cost of substitute goods or services</li>
<li>Personal injury or property damage</li>
</ul>

<p>ARISING OUT OF OR RELATING TO:</p>

<ul>
<li>Your use of or inability to use the System</li>
<li>Any conduct or content of any third party on the System</li>
<li>Any content obtained from the System</li>
<li>Unauthorized access, use, or alteration of your transmissions or content</li>
</ul>

<p>WHETHER BASED ON WARRANTY, CONTRACT, TORT (INCLUDING NEGLIGENCE), OR ANY OTHER LEGAL THEORY, AND WHETHER OR NOT WE HAVE BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.</p>

<p>SOME JURISDICTIONS DO NOT ALLOW THE EXCLUSION OR LIMITATION OF CERTAIN WARRANTIES OR THE LIMITATION OR EXCLUSION OF LIABILITY FOR INCIDENTAL OR CONSEQUENTIAL DAMAGES. ACCORDINGLY, SOME OF THE ABOVE LIMITATIONS MAY NOT APPLY TO YOU.</p>

<h3>10. Indemnification</h3>

<p>You agree to defend, indemnify, and hold harmless the Sibu Resident Office, the Sarawak State Government, and their respective officers, employees, agents, and affiliates from and against any and all claims, damages, obligations, losses, liabilities, costs, and expenses (including but not limited to attorney's fees and costs) arising from:</p>

<ul>
<li>Your use of or access to the System</li>
<li>Your violation of these Terms</li>
<li>Your violation of any third-party right, including without limitation any copyright, property, or privacy right</li>
<li>Any claim that your use of the System caused damage to a third party</li>
<li>Your submission of false, inaccurate, or misleading information</li>
<li>Your negligence or willful misconduct</li>
</ul>

<p>This indemnification obligation will survive the termination of these Terms and your use of the System. We reserve the right to assume the exclusive defense and control of any matter subject to indemnification by you, in which event you will cooperate with us in asserting any available defenses.</p>

<h3>11. Termination</h3>

<p><strong>11.1 Termination by Us</strong></p>

<p>We may terminate or suspend your account and access to the System immediately, without prior notice or liability, for any reason, including but not limited to:</p>

<ul>
<li>Violation of these Terms</li>
<li>Fraudulent, abusive, or illegal activity</li>
<li>Security concerns or threats</li>
<li>Extended periods of inactivity</li>
<li>Changes in your employment or organizational affiliation</li>
<li>Request by law enforcement or government authorities</li>
<li>Discontinuation of the System</li>
</ul>

<p><strong>11.2 Termination by You</strong></p>

<p>You may terminate your account at any time by contacting us and requesting account closure. Upon termination, your right to use the System will immediately cease.</p>

<p><strong>11.3 Effect of Termination</strong></p>

<p>Upon termination of your account:</p>

<ul>
<li>Your access to the System will be immediately revoked</li>
<li>All provisions of these Terms that by their nature should survive termination shall survive, including but not limited to ownership provisions, warranty disclaimers, indemnity, and limitations of liability</li>
<li>We may retain your information as required by law or for legitimate business purposes</li>
<li>You remain responsible for any obligations incurred prior to termination</li>
</ul>

<h3>12. Governing Law and Dispute Resolution</h3>

<p><strong>12.1 Governing Law</strong></p>

<p>These Terms and your use of the System shall be governed by and construed in accordance with the laws of Malaysia, without regard to its conflict of law provisions. You agree to submit to the personal and exclusive jurisdiction of the courts located in Malaysia for the resolution of any disputes.</p>

<p><strong>12.2 Dispute Resolution</strong></p>

<p>In the event of any dispute, controversy, or claim arising out of or relating to these Terms or the System, the parties agree to first attempt to resolve the dispute through good faith negotiations. If the dispute cannot be resolved through negotiation within 30 days, either party may pursue legal remedies in accordance with applicable law.</p>

<p><strong>12.3 Class Action Waiver</strong></p>

<p>To the extent permitted by law, you agree that any dispute resolution proceedings will be conducted only on an individual basis and not in a class, consolidated, or representative action.</p>

<h3>13. General Provisions</h3>

<p><strong>13.1 Entire Agreement</strong></p>

<p>These Terms, together with our Privacy Policy and any other legal notices published by us on the System, constitute the entire agreement between you and us concerning the System and supersede all prior or contemporaneous agreements, representations, warranties, and understandings.</p>

<p><strong>13.2 Severability</strong></p>

<p>If any provision of these Terms is found to be invalid, illegal, or unenforceable by a court of competent jurisdiction, the remaining provisions shall continue in full force and effect. The invalid, illegal, or unenforceable provision shall be deemed modified to the extent necessary to make it valid, legal, and enforceable while preserving its intent.</p>

<p><strong>13.3 Waiver</strong></p>

<p>No waiver of any term or condition of these Terms shall be deemed a further or continuing waiver of such term or condition or any other term or condition. Our failure to assert any right or provision under these Terms shall not constitute a waiver of such right or provision.</p>

<p><strong>13.4 Assignment</strong></p>

<p>You may not assign or transfer these Terms or your rights and obligations under these Terms without our prior written consent. We may assign or transfer these Terms or our rights and obligations under these Terms at any time without restriction.</p>

<p><strong>13.5 Force Majeure</strong></p>

<p>We shall not be liable for any failure or delay in performance under these Terms due to circumstances beyond our reasonable control, including but not limited to acts of God, natural disasters, war, terrorism, riots, embargoes, acts of civil or military authorities, fire, floods, accidents, network infrastructure failures, strikes, or shortages of transportation facilities, fuel, energy, labor, or materials.</p>

<p><strong>13.6 Notices</strong></p>

<p>We may provide notices to you via email, regular mail, or postings on the System. Notices sent by email will be deemed given when sent to the email address associated with your account. You agree that all agreements, notices, disclosures, and other communications that we provide to you electronically satisfy any legal requirement that such communications be in writing.</p>

<h3>14. Contact Information</h3>

<p>If you have any questions, concerns, or complaints regarding these Terms or the System, please contact us at:</p>

<p><strong>Sibu Resident Office</strong><br>
Aras 5, Kompleks Islam Sarawak Sibu<br>
Jalan Awang Ramli Amit<br>
96000 Sibu, Sarawak<br>
Malaysia</p>

<p><strong>Tel:</strong> 084-330202 / 082-318963 / 082-321963<br>
<strong>Fax:</strong> 084-320970 / 082-347701 / 082-317214<br>
<strong>Website:</strong> <a href="https://sibu.sarawak.gov.my" target="_blank">https://sibu.sarawak.gov.my</a><br>
<strong>Email:</strong> <a href="mailto:khairuni90@sarawak.gov.my">khairuni90@sarawak.gov.my</a></p>

<p><em>Last Updated: February 2026</em></p>
HTML;
    }
}
