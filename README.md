## Hot to run?
1.) composer install

2.) symfony server:start

3.) POST request to API http://127.0.0.1:8000/api/v1/basket/calc-cost

````json
{
    "items": {
        "42": {
            "currency": "EUR",
            "price": 49.99,
            "quantity": 1
        },
        "55": {
            "currency": "USD",
            "price": 12,
            "quantity": 3
        }
    },
    "checkoutCurrency": "USD"
}
````