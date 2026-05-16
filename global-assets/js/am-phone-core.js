(function() {
  if (window.PhoneCore) return;

  const countries = [
  // ==================== South Asia (5) ====================
  { name: "India", code: "+91", flag: "🇮🇳", min: 10, max: 10 },
  { name: "Pakistan", code: "+92", flag: "🇵🇰", min: 10, max: 10 },
  { name: "Bangladesh", code: "+880", flag: "🇧🇩", min: 10, max: 10 },
  { name: "Nepal", code: "+977", flag: "🇳🇵", min: 10, max: 10 },
  { name: "Sri Lanka", code: "+94", flag: "🇱🇰", min: 9, max: 9 },

  // ==================== North America (26) ====================
  { name: "United States", code: "+1", flag: "🇺🇸", min: 10, max: 10 },
  { name: "Canada", code: "+1", flag: "🇨🇦", min: 10, max: 10 },
  { name: "Mexico", code: "+52", flag: "🇲🇽", min: 10, max: 10 },
  { name: "Guatemala", code: "+502", flag: "🇬🇹", min: 8, max: 8 },
  { name: "Honduras", code: "+504", flag: "🇭🇳", min: 8, max: 8 },
  { name: "El Salvador", code: "+503", flag: "🇸🇻", min: 8, max: 8 },
  { name: "Nicaragua", code: "+505", flag: "🇳🇮", min: 8, max: 8 },
  { name: "Costa Rica", code: "+506", flag: "🇨🇷", min: 8, max: 8 },
  { name: "Panama", code: "+507", flag: "🇵🇦", min: 8, max: 8 },
  { name: "Cuba", code: "+53", flag: "🇨🇺", min: 8, max: 8 },
  { name: "Jamaica", code: "+1876", flag: "🇯🇲", min: 7, max: 7 },
  { name: "Trinidad and Tobago", code: "+1868", flag: "🇹🇹", min: 7, max: 7 },
  { name: "Dominican Republic", code: "+1849", flag: "🇩🇴", min: 10, max: 10 },
  { name: "Haiti", code: "+509", flag: "🇭🇹", min: 8, max: 8 },
  { name: "Bahamas", code: "+1242", flag: "🇧🇸", min: 7, max: 7 },
  { name: "Barbados", code: "+1246", flag: "🇧🇧", min: 7, max: 7 },
  { name: "Belize", code: "+501", flag: "🇧🇿", min: 7, max: 7 },
  { name: "Antigua and Barbuda", code: "+1268", flag: "🇦🇬", min: 7, max: 7 },
  { name: "Dominica", code: "+1767", flag: "🇩🇲", min: 7, max: 7 },
  { name: "Grenada", code: "+1473", flag: "🇬🇩", min: 7, max: 7 },
  { name: "Saint Kitts and Nevis", code: "+1869", flag: "🇰🇳", min: 7, max: 7 },
  { name: "Saint Lucia", code: "+1758", flag: "🇱🇨", min: 7, max: 7 },
  { name: "Saint Vincent and the Grenadines", code: "+1784", flag: "🇻🇨", min: 7, max: 7 },
  { name: "Greenland", code: "+299", flag: "🇬🇱", min: 6, max: 6 },
  { name: "Bermuda", code: "+1441", flag: "🇧🇲", min: 7, max: 7 },
  { name: "Cayman Islands", code: "+1345", flag: "🇰🇾", min: 7, max: 7 },

  // ==================== South America (13) ====================
  { name: "Brazil", code: "+55", flag: "🇧🇷", min: 11, max: 11 },
  { name: "Argentina", code: "+54", flag: "🇦🇷", min: 10, max: 10 },
  { name: "Chile", code: "+56", flag: "🇨🇱", min: 9, max: 9 },
  { name: "Colombia", code: "+57", flag: "🇨🇴", min: 10, max: 10 },
  { name: "Peru", code: "+51", flag: "🇵🇪", min: 9, max: 9 },
  { name: "Venezuela", code: "+58", flag: "🇻🇪", min: 10, max: 10 },
  { name: "Ecuador", code: "+593", flag: "🇪🇨", min: 9, max: 9 },
  { name: "Bolivia", code: "+591", flag: "🇧🇴", min: 8, max: 8 },
  { name: "Paraguay", code: "+595", flag: "🇵🇾", min: 9, max: 9 },
  { name: "Uruguay", code: "+598", flag: "🇺🇾", min: 8, max: 8 },
  { name: "Guyana", code: "+592", flag: "🇬🇾", min: 7, max: 7 },
  { name: "Suriname", code: "+597", flag: "🇸🇷", min: 7, max: 7 },
  { name: "French Guiana", code: "+594", flag: "🇬🇫", min: 9, max: 9 },

  // ==================== Europe (46) ====================
  { name: "United Kingdom", code: "+44", flag: "🇬🇧", min: 10, max: 10 },
  { name: "Germany", code: "+49", flag: "🇩🇪", min: 10, max: 11 },
  { name: "France", code: "+33", flag: "🇫🇷", min: 9, max: 9 },
  { name: "Italy", code: "+39", flag: "🇮🇹", min: 9, max: 10 },
  { name: "Spain", code: "+34", flag: "🇪🇸", min: 9, max: 9 },
  { name: "Netherlands", code: "+31", flag: "🇳🇱", min: 9, max: 9 },
  { name: "Poland", code: "+48", flag: "🇵🇱", min: 9, max: 9 },
  { name: "Sweden", code: "+46", flag: "🇸🇪", min: 9, max: 9 },
  { name: "Norway", code: "+47", flag: "🇳🇴", min: 8, max: 8 },
  { name: "Denmark", code: "+45", flag: "🇩🇰", min: 8, max: 8 },
  { name: "Ukraine", code: "+380", flag: "🇺🇦", min: 9, max: 9 },
  { name: "Russia", code: "+7", flag: "🇷🇺", min: 10, max: 10 },
  { name: "Switzerland", code: "+41", flag: "🇨🇭", min: 9, max: 10 },
  { name: "Austria", code: "+43", flag: "🇦🇹", min: 10, max: 10 },
  { name: "Belgium", code: "+32", flag: "🇧🇪", min: 9, max: 9 },
  { name: "Ireland", code: "+353", flag: "🇮🇪", min: 9, max: 9 },
  { name: "Portugal", code: "+351", flag: "🇵🇹", min: 9, max: 9 },
  { name: "Greece", code: "+30", flag: "🇬🇷", min: 10, max: 10 },
  { name: "Czech Republic", code: "+420", flag: "🇨🇿", min: 9, max: 9 },
  { name: "Romania", code: "+40", flag: "🇷🇴", min: 9, max: 9 },
  { name: "Hungary", code: "+36", flag: "🇭🇺", min: 9, max: 9 },
  { name: "Finland", code: "+358", flag: "🇫🇮", min: 9, max: 10 },
  { name: "Iceland", code: "+354", flag: "🇮🇸", min: 7, max: 7 },
  { name: "Lithuania", code: "+370", flag: "🇱🇹", min: 8, max: 8 },
  { name: "Latvia", code: "+371", flag: "🇱🇻", min: 8, max: 8 },
  { name: "Estonia", code: "+372", flag: "🇪🇪", min: 7, max: 8 },
  { name: "Bulgaria", code: "+359", flag: "🇧🇬", min: 9, max: 9 },
  { name: "Croatia", code: "+385", flag: "🇭🇷", min: 9, max: 9 },
  { name: "Slovakia", code: "+421", flag: "🇸🇰", min: 9, max: 9 },
  { name: "Slovenia", code: "+386", flag: "🇸🇮", min: 8, max: 8 },
  { name: "Serbia", code: "+381", flag: "🇷🇸", min: 8, max: 9 },
  { name: "Belarus", code: "+375", flag: "🇧🇾", min: 9, max: 9 },
  { name: "Moldova", code: "+373", flag: "🇲🇩", min: 8, max: 8 },
  { name: "Malta", code: "+356", flag: "🇲🇹", min: 8, max: 8 },
  { name: "Cyprus", code: "+357", flag: "🇨🇾", min: 8, max: 8 },
  { name: "Luxembourg", code: "+352", flag: "🇱🇺", min: 9, max: 9 },
  { name: "Bosnia and Herzegovina", code: "+387", flag: "🇧🇦", min: 8, max: 8 },
  { name: "North Macedonia", code: "+389", flag: "🇲🇰", min: 7, max: 7 },
  { name: "Albania", code: "+355", flag: "🇦🇱", min: 9, max: 9 },
  { name: "Montenegro", code: "+382", flag: "🇲🇪", min: 8, max: 8 },
  { name: "Armenia", code: "+374", flag: "🇦🇲", min: 8, max: 8 },
  { name: "Azerbaijan", code: "+994", flag: "🇦🇿", min: 9, max: 9 },
  { name: "Georgia", code: "+995", flag: "🇬🇪", min: 9, max: 9 },
  { name: "Monaco", code: "+377", flag: "🇲🇨", min: 8, max: 9 },
  { name: "San Marino", code: "+378", flag: "🇸🇲", min: 10, max: 10 },
  { name: "Kosovo", code: "+383", flag: "🇽🇰", min: 8, max: 8 },

  // ==================== Middle East (15) ====================
  { name: "UAE", code: "+971", flag: "🇦🇪", min: 9, max: 9 },
  { name: "Saudi Arabia", code: "+966", flag: "🇸🇦", min: 9, max: 9 },
  { name: "Turkey", code: "+90", flag: "🇹🇷", min: 10, max: 10 },
  { name: "Israel", code: "+972", flag: "🇮🇱", min: 9, max: 9 },
  { name: "Iran", code: "+98", flag: "🇮🇷", min: 10, max: 10 },
  { name: "Iraq", code: "+964", flag: "🇮🇶", min: 10, max: 10 },
  { name: "Kuwait", code: "+965", flag: "🇰🇼", min: 8, max: 8 },
  { name: "Bahrain", code: "+973", flag: "🇧🇭", min: 8, max: 8 },
  { name: "Qatar", code: "+974", flag: "🇶🇦", min: 8, max: 8 },
  { name: "Oman", code: "+968", flag: "🇴🇲", min: 8, max: 8 },
  { name: "Jordan", code: "+962", flag: "🇯🇴", min: 9, max: 9 },
  { name: "Lebanon", code: "+961", flag: "🇱🇧", min: 7, max: 8 },
  { name: "Syria", code: "+963", flag: "🇸🇾", min: 9, max: 9 },
  { name: "Yemen", code: "+967", flag: "🇾🇪", min: 9, max: 9 },
  { name: "Palestine", code: "+970", flag: "🇵🇸", min: 9, max: 9 },

  // ==================== Asia Pacific (27) ====================
  { name: "China", code: "+86", flag: "🇨🇳", min: 11, max: 11 },
  { name: "Japan", code: "+81", flag: "🇯🇵", min: 10, max: 11 },
  { name: "South Korea", code: "+82", flag: "🇰🇷", min: 9, max: 10 },
  { name: "Singapore", code: "+65", flag: "🇸🇬", min: 8, max: 8 },
  { name: "Indonesia", code: "+62", flag: "🇮🇩", min: 10, max: 12 },
  { name: "Malaysia", code: "+60", flag: "🇲🇾", min: 9, max: 10 },
  { name: "Thailand", code: "+66", flag: "🇹🇭", min: 9, max: 9 },
  { name: "Vietnam", code: "+84", flag: "🇻🇳", min: 9, max: 10 },
  { name: "Philippines", code: "+63", flag: "🇵🇭", min: 10, max: 10 },
  { name: "Afghanistan", code: "+93", flag: "🇦🇫", min: 9, max: 9 },
  { name: "Myanmar", code: "+95", flag: "🇲🇲", min: 9, max: 10 },
  { name: "Cambodia", code: "+855", flag: "🇰🇭", min: 8, max: 9 },
  { name: "Laos", code: "+856", flag: "🇱🇦", min: 10, max: 10 },
  { name: "Taiwan", code: "+886", flag: "🇹🇼", min: 9, max: 9 },
  { name: "Hong Kong", code: "+852", flag: "🇭🇰", min: 8, max: 8 },
  { name: "Macau", code: "+853", flag: "🇲🇴", min: 8, max: 8 },
  { name: "Mongolia", code: "+976", flag: "🇲🇳", min: 8, max: 8 },
  { name: "North Korea", code: "+850", flag: "🇰🇵", min: 10, max: 10 },
  { name: "Bhutan", code: "+975", flag: "🇧🇹", min: 7, max: 8 },
  { name: "Maldives", code: "+960", flag: "🇲🇻", min: 7, max: 7 },
  { name: "Brunei", code: "+673", flag: "🇧🇳", min: 7, max: 7 },
  { name: "Timor-Leste", code: "+670", flag: "🇹🇱", min: 8, max: 8 },
  { name: "Kazakhstan", code: "+7", flag: "🇰🇿", min: 10, max: 10 },
  { name: "Uzbekistan", code: "+998", flag: "🇺🇿", min: 9, max: 9 },
  { name: "Turkmenistan", code: "+993", flag: "🇹🇲", min: 8, max: 8 },
  { name: "Kyrgyzstan", code: "+996", flag: "🇰🇬", min: 9, max: 9 },
  { name: "Tajikistan", code: "+992", flag: "🇹🇯", min: 9, max: 9 },

  // ==================== Africa (54) ====================
  { name: "South Africa", code: "+27", flag: "🇿🇦", min: 9, max: 9 },
  { name: "Nigeria", code: "+234", flag: "🇳🇬", min: 10, max: 10 },
  { name: "Kenya", code: "+254", flag: "🇰🇪", min: 9, max: 9 },
  { name: "Egypt", code: "+20", flag: "🇪🇬", min: 10, max: 10 },
  { name: "Morocco", code: "+212", flag: "🇲🇦", min: 9, max: 9 },
  { name: "Algeria", code: "+213", flag: "🇩🇿", min: 9, max: 9 },
  { name: "Tunisia", code: "+216", flag: "🇹🇳", min: 8, max: 8 },
  { name: "Libya", code: "+218", flag: "🇱🇾", min: 9, max: 9 },
  { name: "Sudan", code: "+249", flag: "🇸🇩", min: 9, max: 9 },
  { name: "South Sudan", code: "+211", flag: "🇸🇸", min: 9, max: 9 },
  { name: "Ethiopia", code: "+251", flag: "🇪🇹", min: 9, max: 9 },
  { name: "Tanzania", code: "+255", flag: "🇹🇿", min: 9, max: 9 },
  { name: "Uganda", code: "+256", flag: "🇺🇬", min: 9, max: 9 },
  { name: "Rwanda", code: "+250", flag: "🇷🇼", min: 9, max: 9 },
  { name: "Burundi", code: "+257", flag: "🇧🇮", min: 7, max: 7 },
  { name: "Congo (DRC)", code: "+243", flag: "🇨🇩", min: 9, max: 9 },
  { name: "Congo (Republic)", code: "+242", flag: "🇨🇬", min: 9, max: 9 },
  { name: "Cameroon", code: "+237", flag: "🇨🇲", min: 8, max: 8 },
  { name: "Ghana", code: "+233", flag: "🇬🇭", min: 9, max: 10 },
  { name: "Ivory Coast", code: "+225", flag: "🇨🇮", min: 10, max: 10 },
  { name: "Senegal", code: "+221", flag: "🇸🇳", min: 9, max: 9 },
  { name: "Mali", code: "+223", flag: "🇲🇱", min: 8, max: 8 },
  { name: "Guinea", code: "+224", flag: "🇬🇳", min: 8, max: 8 },
  { name: "Burkina Faso", code: "+226", flag: "🇧🇫", min: 8, max: 8 },
  { name: "Niger", code: "+227", flag: "🇳🇪", min: 8, max: 8 },
  { name: "Togo", code: "+228", flag: "🇹🇬", min: 8, max: 8 },
  { name: "Benin", code: "+229", flag: "🇧🇯", min: 8, max: 8 },
  { name: "Mozambique", code: "+258", flag: "🇲🇿", min: 8, max: 9 },
  { name: "Zambia", code: "+260", flag: "🇿🇲", min: 9, max: 9 },
  { name: "Zimbabwe", code: "+263", flag: "🇿🇼", min: 9, max: 9 },
  { name: "Botswana", code: "+267", flag: "🇧🇼", min: 8, max: 8 },
  { name: "Namibia", code: "+264", flag: "🇳🇦", min: 9, max: 9 },
  { name: "Angola", code: "+244", flag: "🇦🇴", min: 9, max: 9 },
  { name: "Madagascar", code: "+261", flag: "🇲🇬", min: 9, max: 9 },
  { name: "Mauritius", code: "+230", flag: "🇲🇺", min: 7, max: 8 },
  { name: "Cabo Verde", code: "+238", flag: "🇨🇻", min: 7, max: 7 },
  { name: "Gabon", code: "+241", flag: "🇬🇦", min: 8, max: 8 },
  { name: "Eritrea", code: "+291", flag: "🇪🇷", min: 7, max: 7 },
  { name: "Djibouti", code: "+253", flag: "🇩🇯", min: 8, max: 8 },
  { name: "Somalia", code: "+252", flag: "🇸🇴", min: 8, max: 9 },
  { name: "Liberia", code: "+231", flag: "🇱🇷", min: 8, max: 8 },
  { name: "Sierra Leone", code: "+232", flag: "🇸🇱", min: 8, max: 8 },
  { name: "Guinea-Bissau", code: "+245", flag: "🇬🇼", min: 7, max: 7 },
  { name: "Gambia", code: "+220", flag: "🇬🇲", min: 7, max: 7 },
  { name: "Mauritania", code: "+222", flag: "🇲🇷", min: 8, max: 8 },
  { name: "Eswatini", code: "+268", flag: "🇸🇿", min: 8, max: 8 },
  { name: "Lesotho", code: "+266", flag: "🇱🇸", min: 8, max: 8 },
  { name: "Malawi", code: "+265", flag: "🇲🇼", min: 9, max: 9 },
  { name: "Chad", code: "+235", flag: "🇹🇩", min: 8, max: 8 },
  { name: "Central African Republic", code: "+236", flag: "🇨🇫", min: 8, max: 8 },
  { name: "Equatorial Guinea", code: "+240", flag: "🇬🇶", min: 9, max: 9 },
  { name: "Seychelles", code: "+248", flag: "🇸🇨", min: 7, max: 7 },
  { name: "Comoros", code: "+269", flag: "🇰🇲", min: 7, max: 7 },
  { name: "São Tomé and Príncipe", code: "+239", flag: "🇸🇹", min: 7, max: 7 },

  // ==================== Oceania (14) ====================
  { name: "Australia", code: "+61", flag: "🇦🇺", min: 9, max: 9 },
  { name: "New Zealand", code: "+64", flag: "🇳🇿", min: 9, max: 10 },
  { name: "Fiji", code: "+679", flag: "🇫🇯", min: 7, max: 7 },
  { name: "Papua New Guinea", code: "+675", flag: "🇵🇬", min: 8, max: 8 },
  { name: "Solomon Islands", code: "+677", flag: "🇸🇧", min: 7, max: 7 },
  { name: "Vanuatu", code: "+678", flag: "🇻🇺", min: 7, max: 7 },
  { name: "Samoa", code: "+685", flag: "🇼🇸", min: 7, max: 7 },
  { name: "Tonga", code: "+676", flag: "🇹🇴", min: 5, max: 5 },
  { name: "Kiribati", code: "+686", flag: "🇰🇮", min: 5, max: 5 },
  { name: "Micronesia", code: "+691", flag: "🇫🇲", min: 7, max: 7 },
  { name: "Marshall Islands", code: "+692", flag: "🇲🇭", min: 7, max: 7 },
  { name: "Palau", code: "+680", flag: "🇵🇼", min: 7, max: 7 },
  { name: "Nauru", code: "+674", flag: "🇳🇷", min: 7, max: 7 },
  { name: "Tuvalu", code: "+688", flag: "🇹🇻", min: 5, max: 5 }
];

window.PhoneCore = (function () {

  /**
   * Returns the complete array of all registered countries.
   * @returns {Array} Array of country objects.
   */
  function getCountries() {
    return countries;
  }

  /**
   * Finds a specific country by its exact dial code.
   * @param {string} code - The dial code (e.g., "+91").
   * @returns {Object|undefined} The matching country object.
   */
  function findCountry(code) {
    return countries.find(c => c.code === code);
  }

  /**
   * Auto-detects the country by reading the dial code prefix from a full phone number string.
   * @param {string} number - The full phone number (e.g., "919876543210").
   * @returns {Object|undefined} The detected country object.
   */
  function detectCountry(number) {
    const clean = number.replace(/\D/g, "");
    return countries.find(c =>
      clean.startsWith(c.code.replace("+", ""))
    );
  }

  /**
   * Validates a phone number based on the minimum and maximum length rules of the selected country.
   * @param {string} number - The raw phone number input.
   * @param {Object} country - The country object to validate against.
   * @returns {Object} Result object containing { valid: boolean, error: string|null }.
   */
  function validate(number, country) {
    const clean = number.replace(/\D/g, "");

    if (!country) return { valid: false, error: "No country selected" };
    if (clean.length < country.min) return { valid: false, error: "Too short" };
    if (clean.length > country.max) return { valid: false, error: "Too long" };

    return { valid: true, error: null };
  }

  /**
   * Formats a raw number string into a cleaner visual format with spaces (e.g., 123 456 7890).
   * @param {string} number - The raw phone number string.
   * @returns {string} The visually formatted phone number.
   */
  function format(number) {
    const clean = number.replace(/\D/g, "");
    return clean.replace(/(\d{3})(\d{3})(\d+)/, "$1 $2 $3");
  }

  /**
   * Returns a new array of all countries sorted alphabetically by their name.
   * @returns {Array} Sorted array of country objects.
   */
  function getCountriesSorted() {
    return [...countries].sort((a, b) => a.name.localeCompare(b.name));
  }

  /**
   * Dynamically filters countries based on user input. 
   * Supports direct name match, dial code match, initials match (e.g., "us" -> United States), and multi-word matching.
   * @param {string} query - The search string typed by the user.
   * @returns {Array} Array of matching country objects.
   */
  function searchCountries(query) {
    if (!query) return getCountriesSorted();
    const lowerQuery = query.toLowerCase().trim();
    
    return getCountriesSorted().filter((c) => {
      const nameLower = c.name.toLowerCase();
      
      // 1. Direct match in name or code
      if (nameLower.includes(lowerQuery) || c.code.includes(lowerQuery)) {
        return true;
      }
      
      // 2. Generic Initials match (e.g., "us" -> United States, "sa" -> South Africa)
      const words = nameLower.split(/[\s-]+/);
      if (words.length > 1) {
        const initials = words.map(w => w[0]).join("");
        if (initials.startsWith(lowerQuery)) {
          return true;
        }
      }
      
      // 3. Multi-word partial match (e.g., "unit sta" -> United States)
      const queryParts = lowerQuery.split(/\s+/);
      if (queryParts.length > 1) {
        const allPartsMatch = queryParts.every(part => nameLower.includes(part));
        if (allPartsMatch) {
          return true;
        }
      }
      
      return false;
    });
  }

  return {
    getCountries,
    getCountriesSorted,
    searchCountries,
    findCountry,
    detectCountry,
    validate,
    format
  };

})();

})();
