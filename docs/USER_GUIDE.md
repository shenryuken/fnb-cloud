# FnB Cloud POS - User Guide

A comprehensive guide to using the FnB Cloud Point of Sale system for restaurant and food service operations.

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Shift Management](#shift-management)
3. [Point of Sale (POS)](#point-of-sale-pos)
4. [Order Management](#order-management)
5. [Kitchen Display System (KDS)](#kitchen-display-system-kds)
6. [Menu Management](#menu-management)
7. [Loyalty Program](#loyalty-program)
8. [Reports](#reports)
9. [Settings](#settings)

---

## Getting Started

### Logging In

1. Navigate to your FnB Cloud URL
2. Enter your email and password
3. Click **Sign In**

### Dashboard Overview

After logging in, you'll see the **Dashboard** with:
- Today's sales summary
- Recent orders
- Quick access to POS and other modules

---

## Shift Management

Shifts track cash flow and sales during work periods. **You must open a shift before processing orders.**

### Opening a Shift

1. Go to **Shifts** from the sidebar
2. Click **Open Shift**
3. Enter the **Opening Cash** amount in the drawer
4. Add optional notes
5. Click **Open Shift**

### During a Shift

#### Recording Cash Movements

Track cash added to or removed from the drawer:

1. Click **Cash Movement** on the active shift
2. Select movement type:
   - **Cash In**: Money added to drawer
   - **Cash Out**: Money removed from drawer
   - **Adjustment**: Corrections
3. Enter amount and reason
4. Click **Save**

#### Viewing Shift Details

Click the eye icon on any shift to view:
- Opening and expected cash
- Total sales and order count
- Cash movements history
- List of orders processed

### Closing a Shift

1. Click **Close Shift** on the active shift
2. Count the actual cash in the drawer
3. Enter the **Actual Cash** amount
4. Review the variance (difference between expected and actual)
5. Add optional closing notes
6. Click **Close Shift**

### Shift History

- Filter shifts by date or cashier
- View detailed reconciliation for each shift
- Track cash variances over time

---

## Point of Sale (POS)

The POS is where you take orders and process payments.

### Interface Overview

- **Left Panel**: Product categories and items
- **Right Panel**: Current cart and checkout
- **Top Bar**: Search, table number, order type

### Taking an Order

#### Adding Items to Cart

**Quick Add (Simple Items):**
- Click on any product to add it directly to the cart

**Products with Options:**
- Click on a product with variants/add-ons
- Select the variant (e.g., size)
- Choose add-ons if available
- Adjust quantity
- Add item notes
- Click **Add to Cart**

**Set Meals:**
- Select items for each component group
- Respect minimum/maximum selections
- Additional items may have extra charges

#### Managing the Cart

- **Adjust Quantity**: Use +/- buttons on cart items
- **Remove Item**: Click the X button
- **Clear Cart**: Click **Clear** to remove all items
- **Hold Order**: Save current order for later

### Order Types

- **Dine In**: Customer eating at the restaurant
- **Takeaway**: Customer taking food to go

### Table Number

Enter the table number for dine-in orders to help with food delivery.

### Applying Discounts

1. Click the **Discount** button in the cart
2. Choose discount type:
   - **Percentage**: e.g., 10% off
   - **Fixed Amount**: e.g., $5 off
3. Enter the value
4. Click **Apply**

### Using Vouchers

1. Click the **Discount** button
2. Go to the **Voucher** tab
3. Enter the voucher code
4. Click **Apply Voucher**

The discount will be calculated automatically based on the voucher type.

### Customer & Loyalty Points

#### Linking a Customer

1. Click the **Discount** button
2. Go to the **Customer** tab
3. Search by name, email, or phone
4. Select the customer

#### Redeeming Points

1. Link a customer first
2. Go to the **Points** tab
3. Enter points to redeem
4. Click **Apply Points**

Points are converted to a discount based on your loyalty settings.

### Processing Payment

1. Click **Pay** when the cart is ready
2. Select payment method:
   - **Cash**: Enter amount received, see change
   - **Card**: Process card payment
   - **E-Wallet**: Process digital wallet payment
3. Click **Complete Payment**

#### Split Payments

For orders paid with multiple methods:

1. Enable **Split Payment** toggle
2. Enter amount for first payment method
3. Click **Add Split**
4. Repeat for remaining balance
5. Complete when fully paid

### After Payment

- Receipt is generated automatically
- Order is sent to kitchen (KDS)
- Points are awarded to linked customer
- Auto-issued vouchers are generated if applicable

### Held Orders

Save orders to complete later:

1. Click **Hold** to save current cart
2. Add a label (e.g., "Table 5" or customer name)
3. Click **Hold Order**

To retrieve:
1. Click **Held Orders**
2. Select the order to restore
3. Continue with checkout

---

## Order Management

View and manage all orders from the **Orders** page.

### Order List

- View all orders with status indicators
- Filter by status, date, or search
- Click any order to view details

### Order Statuses

- **Pending**: Order placed, awaiting kitchen
- **In Progress**: Kitchen is preparing
- **Ready**: Food ready for pickup/serving
- **Completed**: Order fulfilled
- **Cancelled**: Order was cancelled

### Order Actions

- **View Details**: See full order information
- **Print Receipt**: Reprint the receipt
- **Cancel Order**: Cancel unpaid orders

---

## Kitchen Display System (KDS)

The KDS shows orders for kitchen staff to prepare.

### KDS Interface

- Orders displayed as cards
- Color-coded by wait time
- Oldest orders shown first

### Managing Orders

- **Start Order**: Mark as in progress
- **Complete Item**: Mark individual items done
- **Complete Order**: Mark entire order ready
- **Bump**: Remove from display when served

### Kitchen Busy Mode

When overwhelmed:
1. Enable **Kitchen Busy** mode
2. POS will show a warning to cashiers
3. Helps manage customer expectations

---

## Menu Management

### Categories

Organize products into categories:

1. Go to **Categories**
2. Click **Add Category**
3. Enter name and sort order
4. Toggle active status
5. Save

### Products

#### Adding a Product

1. Go to **Products**
2. Click **Add Product**
3. Fill in details:
   - **Name**: Product name
   - **Description**: Optional description
   - **Price**: Base price
   - **Category**: Select category
   - **Product Type**: Ala carte or Set meal
4. Upload an image (optional)
5. Save

#### Product Types

**Ala Carte**: Standard single items

**Set Meal**: Combo with multiple components
- Create groups (e.g., "Choose Main", "Choose Side")
- Set min/max selections per group
- Add products to each group
- Set extra charges for premium options

#### Variants

Add size or style options:

1. Edit a product
2. Go to **Variants** section
3. Add variants (e.g., Small, Medium, Large)
4. Set price for each variant

#### Add-ons

Create optional extras:

1. Go to **Add-ons**
2. Create add-on items (e.g., Extra Cheese, Bacon)
3. Set prices
4. Assign to products or groups

---

## Loyalty Program

### Customers

Manage your customer database:

1. Go to **Customers**
2. **Add Customer**: Name, email, phone
3. **View History**: See order history and stats
4. **Edit Points**: Adjust point balance

### Loyalty Points

Configure in **Settings > Loyalty**:

- **Earn Rate**: Points earned per dollar spent
- **Redeem Rate**: Dollar value per points redeemed
- **Minimum Redeem**: Minimum points for redemption

#### Point Promotions

Run double/triple points campaigns:

1. Enable promotion
2. Set multiplier (e.g., 2x points)
3. Set start and end dates

### Vouchers

Create promotional vouchers:

1. Go to **Vouchers**
2. Click **Add Voucher**
3. Configure:
   - **Code**: Unique voucher code
   - **Type**: Percentage or Fixed discount
   - **Value**: Discount amount
   - **Validity**: Start and end dates
   - **Usage Limits**: Total uses and per-customer limits
   - **Restrictions**: First-time customers only, combinable with other discounts

#### Auto-Issue Vouchers

Vouchers that auto-generate after purchase:

1. Set **Issue on Min Spend** (e.g., $50)
2. Set **Expires in Days** (e.g., 30 days)
3. When a customer spends the minimum, they receive a voucher code on their receipt

---

## Reports

### Sales Report

View sales performance:

- **Date Range**: Filter by Today, 7 Days, Month, or custom
- **Summary Cards**: Orders, Gross Sales, Discounts, Tax, Net Sales
- **Revenue Chart**: Daily sales trend
- **Daily Breakdown**: Detailed daily summary
- **Payment Methods**: Sales by payment type
- **Top Products**: Best-selling items

### Cashier Report

Track cashier performance:

- Filter by date range and cashier
- View per-cashier stats:
  - Total shifts worked
  - Total sales
  - Order count
  - Average order value
  - Shift duration
  - Cash variance

---

## Settings

### Receipt Settings

Customize printed receipts:

- Business name and address
- Header and footer text
- Show/hide tax breakdown
- Logo upload

### Loyalty Settings

Configure points program:

- Earn rate (points per dollar)
- Redeem rate (value per points)
- Minimum redemption threshold
- Promotional multipliers

### Quick Notes

Pre-defined notes for order items:

1. Go to **Settings > Quick Notes**
2. Add common notes (e.g., "No onions", "Extra spicy")
3. Reorder as needed
4. These appear in the POS item modal

### User Management

Manage staff accounts:

1. Go to **Settings > Users**
2. Add new users with email and password
3. Assign roles (Owner, Cashier, Kitchen Staff, Waiter)
4. Each role has specific permissions

### Roles & Permissions

Available roles:

- **Owner**: Full access to all features
- **Cashier**: POS access, view orders
- **Kitchen Staff**: KDS access only
- **Waiter**: POS access, limited settings

---

## Tips & Best Practices

### Daily Workflow

1. **Start of Day**:
   - Open a shift with starting cash
   - Verify products are available

2. **During Service**:
   - Process orders through POS
   - Monitor KDS for order flow
   - Record any cash movements

3. **End of Day**:
   - Count cash drawer
   - Close shift with actual cash
   - Review daily sales report

### Cash Management

- Always record cash-in/cash-out transactions
- Investigate any significant variances
- Review shift reports regularly

### Customer Loyalty

- Encourage customers to join the loyalty program
- Train staff to ask for customer information
- Promote point redemption for repeat visits

### Reporting

- Check sales reports daily
- Monitor top-selling products
- Track cashier performance weekly
- Use data to inform menu decisions

---

## Troubleshooting

### "No Active Shift" Error

You must open a shift before taking orders:
1. Go to **Shifts**
2. Click **Open Shift**
3. Enter opening cash

### Order Not Appearing in KDS

- Verify the order status is "Pending"
- Check that KDS page is refreshed
- Ensure the order was fully paid

### Voucher Not Working

Check:
- Code is entered correctly (case-insensitive)
- Voucher is within valid dates
- Usage limit hasn't been reached
- Customer eligibility (first-time only restrictions)

### Points Not Applying

Ensure:
- Customer is linked to the order
- Customer has sufficient points
- Points meet minimum redemption threshold

---

## Support

For additional help:
- Contact your system administrator
- Submit a support ticket through the help system
- Check for software updates regularly

---

*FnB Cloud POS - Making restaurant management simple and efficient.*
